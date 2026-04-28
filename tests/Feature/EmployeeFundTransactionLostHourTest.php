<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeFundTransaction;
use App\Models\ResponsibilityCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmployeeFundTransactionLostHourTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_lost_hour_minutes_per_selected_employee(): void
    {
        /** @var User $user */
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

    public function test_it_persists_selected_employee_sort_order_for_cwa_listing(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-ORDER',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $firstEmployee = Employee::query()->create([
            'employee_no' => 'EMP-ORDER-1',
            'first_name' => 'Aaron',
            'last_name' => 'Dela Cruz',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'monthly_compensation' => 10000,
            'is_active' => true,
        ]);
        $secondEmployee = Employee::query()->create([
            'employee_no' => 'EMP-ORDER-2',
            'first_name' => 'Bianca',
            'last_name' => 'Reyes',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'monthly_compensation' => 20000,
            'is_active' => true,
        ]);

        $payload = [
            'employee_type' => 'contract_of_service',
            'payee_name' => 'Scholarship Program Payroll',
            'responsibility_center' => $responsibilityCenter->id,
            'employees' => [
                [
                    'employee_record_id' => $firstEmployee->id,
                    'sort_order' => 2,
                    'payee_name' => 'Aaron Dela Cruz',
                    'office' => 'Scholarship Program',
                    'monthly_compensation' => 10000,
                ],
                [
                    'employee_record_id' => $secondEmployee->id,
                    'sort_order' => 1,
                    'payee_name' => 'Bianca Reyes',
                    'office' => 'Scholarship Program',
                    'monthly_compensation' => 20000,
                ],
            ],
        ];

        $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', $payload)
            ->assertCreated()
            ->assertJsonPath('data.employees.0.employee_record_id', $secondEmployee->id)
            ->assertJsonPath('data.employees.0.sort_order', 1)
            ->assertJsonPath('data.employees.1.employee_record_id', $firstEmployee->id)
            ->assertJsonPath('data.employees.1.sort_order', 2);

        $this->assertDatabaseHas('fund_transaction_employees', [
            'employee_record_id' => $firstEmployee->id,
            'sort_order' => 2,
        ]);
        $this->assertDatabaseHas('fund_transaction_employees', [
            'employee_record_id' => $secondEmployee->id,
            'sort_order' => 1,
        ]);
    }

    public function test_it_persists_payee_and_agency_for_project_based_transactions(): void
    {
        /** @var User $user */
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

    public function test_it_updates_status_and_basic_obr_fields_through_the_status_endpoint(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-STATUS',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $employee = Employee::query()->create([
            'employee_no' => 'EMP-9010',
            'first_name' => 'Rina',
            'last_name' => 'Santos',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $payload = [
            'employee_type' => 'contract_of_service',
            'payee_name' => 'Rina Santos',
            'responsibility_center' => $responsibilityCenter->id,
            'employees' => [[
                'employee_record_id' => $employee->id,
                'payee_name' => 'Rina Santos',
                'office' => 'Scholarship Program',
            ]],
        ];

        $createResponse = $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', $payload)
            ->assertCreated()
            ->assertJsonPath('data.transaction_status', 'on_process');

        $transactionId = $createResponse->json('data.id');

        $this->actingAs($user)
            ->patchJson("/api/employee-fund-transactions/{$transactionId}/update-status", [
                'transaction_status' => 'claimed',
                'fiscal_year' => 2026,
                'obr_no' => '200-26-01-0001',
            ])
            ->assertOk()
            ->assertJsonPath('data.transaction_status', 'claimed')
            ->assertJsonPath('data.fiscal_year', '2026')
            ->assertJsonPath('data.obr_no', '200-26-01-0001');

        $this->assertDatabaseHas('employee_fund_transactions', [
            'id' => $transactionId,
            'transaction_status' => 'claimed',
            'fiscal_year' => '2026',
            'obr_no' => '200-26-01-0001',
        ]);
    }

    public function test_it_proxies_obr_tracking_info(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        Http::fake([
            'https://tracking.pgpict.com/api/obr-tracking-info*' => Http::response([
                'tracking_information' => [
                    [
                        'trn_remarks' => 'OBR received by accounting',
                        'trn_date' => '2026-04-28 09:15:00',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/obr-tracking-info?fiscal_year=2026&obr_no=200-26-01-0001&dv_no=DV-2026-0001&type=REGULAR')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tracking_information.0.trn_remarks', 'OBR received by accounting')
            ->assertJsonPath('data.tracking_information.0.trn_date', '2026-04-28 09:15:00');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://tracking.pgpict.com/api/obr-tracking-info?fiscal_year=2026&obr_no=200-26-01-0001&dv_no=DV-2026-0001&type=REGULAR';
        });
    }

    public function test_it_normalizes_transaction_status_labels_before_validation(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-STATUS-LABEL',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $employee = Employee::query()->create([
            'employee_no' => 'EMP-9012',
            'first_name' => 'Nina',
            'last_name' => 'Flores',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $createResponse = $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', [
                'employee_type' => 'contract_of_service',
                'payee_name' => 'Nina Flores',
                'responsibility_center' => $responsibilityCenter->id,
                'transaction_status' => 'On Process',
                'employees' => [[
                    'employee_record_id' => $employee->id,
                    'payee_name' => 'Nina Flores',
                    'office' => 'Scholarship Program',
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.transaction_status', 'on_process');

        $transactionId = $createResponse->json('data.id');

        $this->actingAs($user)
            ->patchJson("/api/employee-fund-transactions/{$transactionId}/update-status", [
                'transaction_status' => 'Claimed',
            ])
            ->assertOk()
            ->assertJsonPath('data.transaction_status', 'claimed');
    }

    public function test_it_rejects_pending_as_a_transaction_status(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-STATUS-REJECT',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $employee = Employee::query()->create([
            'employee_no' => 'EMP-9011',
            'first_name' => 'Liza',
            'last_name' => 'Diaz',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $createResponse = $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', [
                'employee_type' => 'contract_of_service',
                'payee_name' => 'Liza Diaz',
                'responsibility_center' => $responsibilityCenter->id,
                'employees' => [[
                    'employee_record_id' => $employee->id,
                    'payee_name' => 'Liza Diaz',
                    'office' => 'Scholarship Program',
                ]],
            ])
            ->assertCreated();

        $transactionId = $createResponse->json('data.id');

        $this->actingAs($user)
            ->patchJson("/api/employee-fund-transactions/{$transactionId}/update-status", [
                'transaction_status' => 'pending',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['transaction_status']);
    }

    public function test_it_generates_a_new_transaction_id_after_a_soft_deleted_record(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();
        $responsibilityCenter = ResponsibilityCenter::query()->create([
            'code' => 'RC-003',
            'name' => 'Scholarship Program',
            'fiscal_year' => '2026',
        ]);
        $employee = Employee::query()->create([
            'employee_no' => 'EMP-9003',
            'first_name' => 'Tina',
            'last_name' => 'Garcia',
            'office' => 'Scholarship Program',
            'employee_type' => 'contract_of_service',
            'is_active' => true,
        ]);

        $payload = [
            'employee_type' => 'contract_of_service',
            'payee_name' => 'Tina Garcia',
            'responsibility_center' => $responsibilityCenter->id,
            'employees' => [[
                'employee_record_id' => $employee->id,
                'payee_name' => 'Tina Garcia',
                'office' => 'Scholarship Program',
            ]],
        ];

        $firstResponse = $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', $payload)
            ->assertCreated();

        $firstRecord = EmployeeFundTransaction::query()->findOrFail($firstResponse->json('data.id'));
        $firstTransactionId = $firstRecord->transaction_id;

        $firstRecord->delete();

        $this->assertSoftDeleted('employee_fund_transactions', [
            'id' => $firstRecord->id,
        ]);

        $secondResponse = $this->actingAs($user)
            ->postJson('/api/employee-fund-transactions', $payload)
            ->assertCreated();

        $secondTransactionId = $secondResponse->json('data.transaction_id');

        $this->assertNotSame($firstTransactionId, $secondTransactionId);
        $this->assertSame(
            (int) substr($firstTransactionId, -4) + 1,
            (int) substr($secondTransactionId, -4),
        );
    }
}
