<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\ResponsibilityCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeFundTransactionLostHourTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_lost_hour_minutes_per_selected_employee(): void
    {
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-001',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $employee = Employee::query()->create([
            'employee_no' => 'EMP-9001',
            'first_name' => 'Lia',
            'last_name' => 'Reyes',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $payload = [
            'employee_type' => 'contract_of_service',
            'payee_name' => 'Lia Reyes',
            'responsibility_center' => $responsibilityCenter->id,
            'employees' => [[
                'employee_record_id' => $employee->id,
                'payee_name' => 'Lia Reyes',
                'office' => 'Scholarship Program',
                'lost_hour_minutes' => 95,
            ]],
        ];

        $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', $payload)
            ->assertCreated()
            ->assertJsonPath('data.employees.0.lost_hour_minutes', 95);

        $this->assertDatabaseHas('fund_transaction_employees', [
            'employee_record_id' => $employee->id,
            'lost_hour_minutes' => 95,
        ]);
    }

    public function test_it_persists_payee_and_agency_for_project_based_transactions(): void
    {
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-002',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $employee = Employee::query()->create([
            'employee_no' => 'EMP-9002',
            'first_name' => 'Mina',
            'last_name' => 'Lopez',
            'office' => 'Field Office',
            'agency' => 'Scholarship Outreach',
            'amount' => 18500.50,
            'employee_type' => 'project_based',
            'is_active' => true,
        ]);

        $payload = [
            'employee_type' => 'project_based',
            'payee_name' => 'Project Payroll',
            'agency' => 'Scholarship Outreach',
            'office' => 'Field Office',
            'amount' => 18500.50,
            'responsibility_center' => $responsibilityCenter->id,
            'employees' => [[
                'employee_record_id' => $employee->id,
                'payee_name' => 'Mina Lopez',
                'office' => 'Field Office',
                'amount' => 18500.50,
            ]],
        ];

        $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', $payload)
            ->assertCreated()
            ->assertJsonPath('data.payee_name', 'Project Payroll')
            ->assertJsonPath('data.agency', 'Scholarship Outreach')
            ->assertJsonPath('data.office', 'Field Office')
            ->assertJsonPath('data.amount', '18500.50')
            ->assertJsonPath('data.employees.0.amount', '18500.50');

        $this->assertDatabaseHas('employee_fund_transactions', [
            'employee_type' => 'project_based',
            'payee_name' => 'Project Payroll',
            'agency' => 'Scholarship Outreach',
            'office' => 'Field Office',
            'amount' => '18500.50',
        ]);

        $this->assertDatabaseHas('fund_transaction_employees', [
            'employee_record_id' => $employee->id,
            'amount' => '18500.50',
        ]);
    }
}
