<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProjectBasedFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
    }

    public function test_it_persists_project_based_agency_and_amount_on_create(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('admin');

        $payload = [
            'employee_no' => 'PB-1001',
            'first_name' => 'Aira',
            'last_name' => 'Santos',
            'office' => 'Scholarship Office',
            'employee_type' => 'project_based',
            'agency' => 'Scholarship Outreach Office',
            'amount' => 18500.50,
        ];

        $this->actingAs($user)
            ->postJson('/api/employees', $payload)
            ->assertCreated()
            ->assertJsonPath('data.agency', 'Scholarship Outreach Office')
            ->assertJsonPath('data.amount', '18500.50');

        $this->assertDatabaseHas('employees', [
            'employee_no' => 'PB-1001',
            'employee_type' => 'project_based',
            'agency' => 'Scholarship Outreach Office',
            'amount' => '18500.50',
        ]);
    }

    public function test_it_persists_project_based_agency_and_amount_on_update(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('admin');
        $employee = Employee::query()->create([
            'employee_no' => 'PB-1002',
            'first_name' => 'Mila',
            'last_name' => 'Reyes',
            'office' => 'Scholarship Office',
            'employee_type' => 'project_based',
            'agency' => 'Initial Agency',
            'amount' => 12000,
            'is_active' => true,
        ]);

        $payload = [
            'employee_no' => 'PB-1002',
            'first_name' => 'Mila',
            'last_name' => 'Reyes',
            'office' => 'Scholarship Office',
            'employee_type' => 'project_based',
            'agency' => 'Updated Implementing Agency',
            'amount' => 21500.75,
        ];

        $this->actingAs($user)
            ->putJson('/api/employees/' . $employee->id, $payload)
            ->assertOk()
            ->assertJsonPath('data.agency', 'Updated Implementing Agency')
            ->assertJsonPath('data.amount', '21500.75');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'agency' => 'Updated Implementing Agency',
            'amount' => '21500.75',
        ]);
    }
}
