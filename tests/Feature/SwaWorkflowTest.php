<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\SwaReport;
use App\Models\SwaReportTask;
use App\Models\SwaReportTaskDailyValue;
use App\Models\CalendarEvent;
use App\Models\Signatory;
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
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'username' => 'swa-admin',
            'office' => 'Scholarship Program',
            'designation' => 'Program Coordinator',
        ]);
        $admin->assignRole('admin');

        $officeHead = $this->officeHeadSignatory();

        $this->actingAs($admin)
            ->getJson('/api/swa/personal')
            ->assertOk()
            ->assertJsonPath('subject.office', 'Scholarship Program')
            ->assertJsonPath('subject.designation', 'Program Coordinator')
            ->assertJsonPath('office_heads.0.id', $officeHead->id);

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
            ], $officeHead->id, ['Program Manager']))
            ->assertCreated()
            ->assertJsonPath('data.module_type', 'personal')
            ->assertJsonPath('data.task_count', 5);

        $this->assertSame(5, SwaTask::query()->count());
        $this->assertDatabaseHas('swa_reports', [
            'module_type' => 'personal',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
            'office_head_signatory_id' => $officeHead->id,
            'signatory_name' => $officeHead->name,
            'signatory_office' => $officeHead->office,
        ]);
        $this->assertSame(1, SwaReport::query()->count());
        $this->assertSame(5, SwaReportTask::query()->count());
        $this->assertSame(15, SwaReportTaskDailyValue::query()->count());
        $this->assertSame(['Program Manager'], SwaReport::query()->value('signatory_titles'));
        $this->assertDatabaseMissing('swa_report_task_daily_values', [
            'work_date' => '2026-04-02',
        ]);
    }

    public function test_admin_can_save_employee_tasks_and_generate_employee_swa(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'username' => 'swa-admin-employee',
        ]);
        $admin->assignRole('admin');
        $officeHead = $this->officeHeadSignatory();

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
            ->postJson("/api/swa/employees/{$employee->id}/reports", $this->reportPayload(null, $officeHead->id))
            ->assertCreated()
            ->assertJsonPath('data.module_type', 'employee')
            ->assertJsonPath('data.task_count', 5);

        $this->assertDatabaseHas('swa_reports', [
            'module_type' => 'employee',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
            'office_head_signatory_id' => $officeHead->id,
        ]);
    }

    public function test_admin_can_update_and_delete_personal_swa(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'username' => 'swa-admin-update',
        ]);
        $admin->assignRole('admin');

        $officeHead = $this->officeHeadSignatory();

        $this->actingAs($admin)
            ->putJson('/api/swa/personal/tasks', ['tasks' => $this->taskPayload()])
            ->assertOk();

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/swa/personal/reports', $this->reportPayload(null, $officeHead->id, ['Program Manager']))
            ->assertCreated();

        $reportId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/swa/personal/reports/{$reportId}", [
                'period_start_date' => '2026-04-06',
                'period_end_date' => '2026-04-08',
                'office_head_id' => $officeHead->id,
                'signatory_titles' => ['Scholarship Coordinator'],
                'work_days' => ['monday', 'tuesday', 'wednesday'],
                'draft_rows' => $this->reportPayload(['2026-04-06', '2026-04-07', '2026-04-08'], $officeHead->id)['draft_rows'],
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $reportId)
            ->assertJsonPath('data.period_start_date', '2026-04-06')
            ->assertJsonPath('data.period_end_date', '2026-04-08');

        $report = SwaReport::query()->findOrFail($reportId);

        $this->assertSame('2026-04-06', $report->period_start_date?->toDateString());
        $this->assertSame('2026-04-08', $report->period_end_date?->toDateString());
        $this->assertSame(['monday', 'tuesday', 'wednesday'], $report->work_days);
        $this->assertSame(['Scholarship Coordinator'], $report->signatory_titles);
        $this->assertSame(15, SwaReportTaskDailyValue::query()->count());
        $this->assertDatabaseMissing('swa_report_task_daily_values', [
            'work_date' => '2026-04-01',
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/swa/personal/reports/{$reportId}")
            ->assertOk();

        $this->assertDatabaseMissing('swa_reports', ['id' => $reportId]);
        $this->assertSame(0, SwaReportTask::query()->count());
        $this->assertSame(0, SwaReportTaskDailyValue::query()->count());
    }

    public function test_admin_can_update_and_delete_employee_swa(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'username' => 'swa-admin-employee-update',
        ]);
        $admin->assignRole('admin');
        $officeHead = $this->officeHeadSignatory();

        $employee = Employee::query()->create([
            'employee_no' => 'EMP-002',
            'first_name' => 'Jose',
            'last_name' => 'Reyes',
            'office' => 'Scholarship Program',
            'designation' => 'Administrative Officer',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/swa/employees/{$employee->id}/tasks", ['tasks' => $this->taskPayload()])
            ->assertOk();

        $createResponse = $this->actingAs($admin)
            ->postJson("/api/swa/employees/{$employee->id}/reports", $this->reportPayload(null, $officeHead->id, ['Program Manager']))
            ->assertCreated();

        $reportId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/swa/employees/{$employee->id}/reports/{$reportId}", [
                'period_start_date' => '2026-04-06',
                'period_end_date' => '2026-04-08',
                'office_head_id' => $officeHead->id,
                'signatory_titles' => ['Scholarship Coordinator'],
                'work_days' => ['monday', 'tuesday', 'wednesday'],
                'draft_rows' => $this->reportPayload(['2026-04-06', '2026-04-07', '2026-04-08'], $officeHead->id)['draft_rows'],
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $reportId)
            ->assertJsonPath('data.period_start_date', '2026-04-06');

        $employeeReport = SwaReport::query()->findOrFail($reportId);
        $this->assertSame(['Scholarship Coordinator'], $employeeReport->signatory_titles);

        $this->actingAs($admin)
            ->deleteJson("/api/swa/employees/{$employee->id}/reports/{$reportId}")
            ->assertOk();

        $this->assertDatabaseMissing('swa_reports', ['id' => $reportId]);
    }

    public function test_employee_swa_search_requires_query_and_only_returns_cos_employees(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne([
            'username' => 'swa-admin-search',
        ]);
        $admin->assignRole('admin');

        Employee::query()->create([
            'employee_no' => 'COS-001',
            'first_name' => 'Lara',
            'last_name' => 'Santos',
            'office' => 'Scholarship Program',
            'designation' => 'Program Aide',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        Employee::query()->create([
            'employee_no' => 'PB-001',
            'first_name' => 'Lara',
            'last_name' => 'Cruz',
            'office' => 'Scholarship Program',
            'designation' => 'Program Aide',
            'employee_type' => 'project_based',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/swa/employees')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('filtered_total', 0);

        $this->actingAs($admin)
            ->getJson('/api/swa/employees?search=Lara')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee_no', 'COS-001')
            ->assertJsonPath('data.0.employee_type', 'contract_of_service');
    }

    private function officeHeadSignatory(): Signatory
    {
        return Signatory::query()->create([
            'part' => 'A',
            'name' => 'Ana Garcia',
            'office' => 'Office of the Governor',
            'title' => ['Program Manager', 'Scholarship Coordinator'],
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

    private function reportPayload(?array $dates = null, ?int $officeHeadId = null, ?array $signatoryTitles = null): array
    {
        $dates = $dates ?? ['2026-04-01', '2026-04-02', '2026-04-06', '2026-04-07'];

        return [
            'period_start_date' => '2026-04-01',
            'period_end_date' => '2026-04-07',
            'office_head_id' => $officeHeadId,
            'signatory_titles' => $signatoryTitles,
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
