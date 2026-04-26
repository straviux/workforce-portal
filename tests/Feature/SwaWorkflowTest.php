<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\SwaReport;
use App\Models\SwaReportTask;
use App\Models\SwaReportTaskDailyValue;
use App\Models\CalendarEvent;
use App\Models\SwaTask;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
    }

    public function test_admin_can_save_personal_tasks_and_generate_personal_swa(): void
    {
        $admin = User::factory()->createOne([
            'username' => 'swa-admin',
        ]);
        $admin->assignRole('admin');

        $taskPayload = $this->taskPayload();

        $this->actingAs($admin)
            ->putJson('/api/swa/personal/tasks', ['tasks' => $taskPayload])
            ->assertOk()
            ->assertJsonCount(5, 'tasks');

        CalendarEvent::query()->create([
            'event_date' => '2026-04-02',
            'title' => 'Declared Work Suspension',
            'event_type' => 'work_suspension',
            'description' => 'No SWA entries should be drafted for this day.',
            'is_system' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->postJson('/api/swa/personal/reports', $this->reportPayload([
                '2026-04-01',
                '2026-04-06',
                '2026-04-07',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.module_type', 'personal')
            ->assertJsonPath('data.task_count', 5);

        $this->assertSame(5, SwaTask::query()->count());
        $this->assertDatabaseHas('swa_reports', [
            'module_type' => 'personal',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
        ]);
        $this->assertSame(1, SwaReport::query()->count());
        $this->assertSame(5, SwaReportTask::query()->count());
        $this->assertSame(15, SwaReportTaskDailyValue::query()->count());
        $this->assertDatabaseMissing('swa_report_task_daily_values', [
            'work_date' => '2026-04-02',
        ]);
    }

    public function test_admin_can_save_employee_tasks_and_generate_employee_swa(): void
    {
        $admin = User::factory()->createOne([
            'username' => 'swa-admin-employee',
        ]);
        $admin->assignRole('admin');

        $employee = Employee::query()->create([
            'employee_no' => 'EMP-001',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'office' => 'HRMO',
            'designation' => 'Administrative Aide VI',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/swa/employees/{$employee->id}/tasks", ['tasks' => $this->taskPayload()])
            ->assertOk()
            ->assertJsonCount(5, 'tasks');

        $this->actingAs($admin)
            ->postJson("/api/swa/employees/{$employee->id}/reports", $this->reportPayload())
            ->assertCreated()
            ->assertJsonPath('data.module_type', 'employee')
            ->assertJsonPath('data.task_count', 5);

        $this->assertDatabaseHas('swa_reports', [
            'module_type' => 'employee',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);
    }

    private function taskPayload(): array
    {
        return [
            ['sort_order' => 1, 'task_name' => 'Prepare accomplishment report', 'task_type' => 'countable'],
            ['sort_order' => 2, 'task_name' => 'File service records', 'task_type' => 'countable'],
            ['sort_order' => 3, 'task_name' => 'Attend meetings', 'task_type' => 'check_blank'],
            ['sort_order' => 4, 'task_name' => 'Review submissions', 'task_type' => 'countable'],
            ['sort_order' => 5, 'task_name' => 'Submit end-of-day summary', 'task_type' => 'check_blank'],
        ];
    }

    private function reportPayload(?array $dates = null): array
    {
        $dates = $dates ?? ['2026-04-01', '2026-04-02', '2026-04-06', '2026-04-07'];

        return [
            'period_start_date' => '2026-04-01',
            'period_end_date' => '2026-04-07',
            'work_days' => ['monday', 'tuesday', 'wednesday', 'thursday'],
            'draft_rows' => [
                [
                    'sort_order' => 1,
                    'daily_values' => collect($dates)->map(fn($date, $index) => [
                        'work_date' => $date,
                        'numeric_value' => $index + 1,
                        'mark_value' => null,
                    ])->all(),
                ],
                [
                    'sort_order' => 2,
                    'daily_values' => collect($dates)->map(fn($date, $index) => [
                        'work_date' => $date,
                        'numeric_value' => ($index + 1) * 2,
                        'mark_value' => null,
                    ])->all(),
                ],
                [
                    'sort_order' => 3,
                    'daily_values' => collect($dates)->map(fn($date, $index) => [
                        'work_date' => $date,
                        'numeric_value' => null,
                        'mark_value' => $index % 2 === 0 ? 'check' : 'dash',
                    ])->all(),
                ],
                [
                    'sort_order' => 4,
                    'daily_values' => collect($dates)->map(fn($date, $index) => [
                        'work_date' => $date,
                        'numeric_value' => ($index + 1) * 3,
                        'mark_value' => null,
                    ])->all(),
                ],
                [
                    'sort_order' => 5,
                    'daily_values' => collect($dates)->map(fn($date, $index) => [
                        'work_date' => $date,
                        'numeric_value' => null,
                        'mark_value' => $index % 2 === 0 ? 'dash' : 'check',
                    ])->all(),
                ],
            ],
        ];
    }
}
