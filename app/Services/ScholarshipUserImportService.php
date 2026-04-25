<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Role;

class ScholarshipUserImportService
{
    public function import(): array
    {
        $connection = DB::connection('scholarship');
        $schema = $connection->getSchemaBuilder();
        $targetSchema = User::query()->getConnection()->getSchemaBuilder();
        $targetHasOfficeDesignation = $targetSchema->hasColumn('users', 'office_designation');

        if (!$schema->hasTable('users')) {
            throw new RuntimeException('Scholarship users table was not found.');
        }

        $missingRoles = collect(['admin', 'staff'])->diff(
            Role::query()
                ->where('guard_name', 'web')
                ->whereIn('name', ['admin', 'staff'])
                ->pluck('name')
                ->all(),
        );

        if ($missingRoles->isNotEmpty()) {
            throw new RuntimeException('Missing required workforce roles: ' . $missingRoles->implode(', '));
        }

        $sourceUsers = $connection->table('users')
            ->select(['name', 'username', 'email', 'password', 'office_designation'])
            ->whereNotNull('username')
            ->orderBy('id')
            ->get();

        $summary = [
            'imported' => 0,
            'created' => 0,
            'updated' => 0,
            'admin_assigned' => 0,
            'staff_assigned' => 0,
            'email_conflicts' => 0,
        ];

        DB::transaction(function () use ($sourceUsers, $targetHasOfficeDesignation, &$summary): void {
            foreach ($sourceUsers as $sourceUser) {
                $roleName = $sourceUser->username === 'admin' ? 'admin' : 'staff';
                $summary[$roleName === 'admin' ? 'admin_assigned' : 'staff_assigned']++;

                $user = User::query()->where('username', $sourceUser->username)->first();
                $isNewUser = !$user;

                if (!$user) {
                    $user = new User();
                }

                $resolvedEmail = $this->resolveEmail($sourceUser->email, $sourceUser->username);

                if ($sourceUser->email && $resolvedEmail === null) {
                    $summary['email_conflicts']++;
                }

                $attributes = [
                    'name' => $sourceUser->name,
                    'username' => $sourceUser->username,
                    'email' => $resolvedEmail,
                    'password' => $sourceUser->password,
                ];

                if ($targetHasOfficeDesignation) {
                    $attributes['office_designation'] = $sourceUser->office_designation;
                }

                $user->forceFill($attributes);

                $user->save();
                $user->syncRoles([$roleName]);

                $summary[$isNewUser ? 'created' : 'updated']++;
            }
        });

        $summary['imported'] = $summary['created'] + $summary['updated'];

        return $summary;
    }

    private function resolveEmail(?string $email, string $username): ?string
    {
        $normalizedEmail = trim((string) $email);

        if ($normalizedEmail === '') {
            return null;
        }

        $emailTakenByAnotherUser = User::query()
            ->where('email', $normalizedEmail)
            ->where('username', '!=', $username)
            ->exists();

        return $emailTakenByAnotherUser ? null : $normalizedEmail;
    }
}
