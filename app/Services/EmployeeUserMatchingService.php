<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeUserMatchingService
{
    /**
     * Confidence (0-100) a suggested match must reach before it's offered for confirmation.
     */
    private const MATCH_THRESHOLD = 55.0;

    /**
     * For every employee without a linked portal account, suggest the best-scoring
     * unassigned user by name similarity.
     */
    public function suggestMatches(): array
    {
        $employees = Employee::query()->whereNull('user_id')->orderBy('last_name')->get();

        $assignedUserIds = Employee::query()->whereNotNull('user_id')->pluck('user_id');
        $candidateUsers = User::query()->whereNotIn('id', $assignedUserIds)->orderBy('name')->get();

        return $employees->map(function (Employee $employee) use ($candidateUsers) {
            $best = null;
            $bestScore = 0.0;

            foreach ($candidateUsers as $user) {
                $score = $this->similarityScore($employee->full_name, $user->name);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $user;
                }
            }

            $suggestedUser = $best && $bestScore >= self::MATCH_THRESHOLD ? $best : null;

            return [
                'employee' => [
                    'id' => $employee->id,
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'office' => $employee->office,
                ],
                'suggested_user' => $suggestedUser ? [
                    'id' => $suggestedUser->id,
                    'name' => $suggestedUser->name,
                    'username' => $suggestedUser->username,
                ] : null,
                'score' => $suggestedUser ? round($bestScore, 1) : 0.0,
            ];
        })->values()->all();
    }

    /**
     * Link the confirmed employee/user pairs. Pairs pointing at an already-linked
     * employee or an already-linked user are silently skipped (stale suggestion).
     */
    public function confirmMatches(array $pairs): array
    {
        $linked = 0;
        $skipped = 0;

        DB::transaction(function () use ($pairs, &$linked, &$skipped) {
            foreach ($pairs as $pair) {
                $employee = Employee::query()->whereNull('user_id')->find($pair['employee_id']);
                $userAlreadyLinked = Employee::query()->where('user_id', $pair['user_id'])->exists();

                if (! $employee || $userAlreadyLinked) {
                    $skipped++;

                    continue;
                }

                $employee->update(['user_id' => $pair['user_id']]);
                $linked++;
            }
        });

        return ['linked' => $linked, 'skipped' => $skipped];
    }

    private function similarityScore(string $a, string $b): float
    {
        $normalizedA = $this->normalizeName($a);
        $normalizedB = $this->normalizeName($b);

        if ($normalizedA === '' || $normalizedB === '') {
            return 0.0;
        }

        similar_text($normalizedA, $normalizedB, $percent);

        return $percent;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name) ?? '';

        return trim(preg_replace('/\s+/', ' ', $name) ?? '');
    }
}
