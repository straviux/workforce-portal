<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScholarshipUserImportTest extends TestCase
{
    use RefreshDatabase;

    private string $scholarshipDatabasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
        $this->configureScholarshipConnection();
        $this->createScholarshipUsersTable();
    }

    protected function tearDown(): void
    {
        DB::purge('scholarship');

        if (isset($this->scholarshipDatabasePath) && file_exists($this->scholarshipDatabasePath)) {
            @unlink($this->scholarshipDatabasePath);
        }

        parent::tearDown();
    }

    public function test_admin_can_import_users_from_scholarship_database(): void
    {
        $admin = $this->makeAdminUser('portal-admin');

        $existingUser = User::factory()->create([
            'name' => 'Legacy Arcee',
            'username' => 'arcee',
            'email' => 'legacy-arcee@example.com',
            'password' => bcrypt('legacy-secret'),
        ]);
        $existingUser->assignRole('admin');

        $conflictUser = User::factory()->create([
            'username' => 'existing-email-owner',
            'email' => 'guest@example.com',
        ]);
        $conflictUser->assignRole('staff');

        $this->insertScholarshipUser([
            'name' => 'Scholarship Admin',
            'username' => 'admin',
            'email' => 'admin@scholarship.local',
            'password' => bcrypt('admin-secret'),
            'office_designation' => 'Scholarship Administrator',
        ]);

        $this->insertScholarshipUser([
            'name' => 'Arcee Migrated',
            'username' => 'arcee',
            'email' => 'arcee@scholarship.local',
            'password' => bcrypt('arcee-secret'),
            'office_designation' => 'Scholarship Staff',
        ]);

        $this->insertScholarshipUser([
            'name' => 'Guest Migrated',
            'username' => 'guest',
            'email' => 'guest@example.com',
            'password' => bcrypt('guest-secret'),
            'office_designation' => 'External User',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/users/import-scholarship');

        $response
            ->assertOk()
            ->assertJsonPath('summary.imported', 3)
            ->assertJsonPath('summary.created', 2)
            ->assertJsonPath('summary.updated', 1)
            ->assertJsonPath('summary.admin_assigned', 1)
            ->assertJsonPath('summary.staff_assigned', 2)
            ->assertJsonPath('summary.email_conflicts', 1);

        $importedAdmin = User::query()->where('username', 'admin')->firstOrFail();
        $importedArcee = User::query()->where('username', 'arcee')->firstOrFail();
        $importedGuest = User::query()->where('username', 'guest')->firstOrFail();

        $this->assertSame('Scholarship Admin', $importedAdmin->name);
        $this->assertTrue($importedAdmin->hasRole('admin'));

        $this->assertSame('Arcee Migrated', $importedArcee->name);
        $this->assertSame('arcee@scholarship.local', $importedArcee->email);
        $this->assertTrue($importedArcee->hasRole('staff'));
        $this->assertFalse($importedArcee->hasRole('admin'));

        $this->assertSame('Guest Migrated', $importedGuest->name);
        $this->assertNull($importedGuest->email);
        $this->assertTrue($importedGuest->hasRole('staff'));
    }

    private function configureScholarshipConnection(): void
    {
        $this->scholarshipDatabasePath = tempnam(sys_get_temp_dir(), 'scholarship-users-');

        config()->set('database.connections.scholarship', [
            'driver' => 'sqlite',
            'database' => $this->scholarshipDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('scholarship');
    }

    private function createScholarshipUsersTable(): void
    {
        Schema::connection('scholarship')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('office_designation')->nullable();
            $table->timestamps();
        });
    }

    private function insertScholarshipUser(array $attributes): void
    {
        DB::connection('scholarship')->table('users')->insert([
            ...$attributes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeAdminUser(string $username): User
    {
        $user = User::factory()->create([
            'username' => $username,
        ]);
        $user->assignRole('admin');

        return $user;
    }
}
