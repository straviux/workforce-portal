<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Certification;
use App\Models\Signatory;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
    }

    public function test_staff_cannot_access_user_and_role_management_endpoints(): void
    {
        $staff = $this->makeStaffUser('staff-viewer');

        $this->actingAs($staff)
            ->getJson('/api/users')
            ->assertForbidden();

        $this->actingAs($staff)
            ->getJson('/api/roles')
            ->assertForbidden();
    }

    public function test_staff_can_view_reference_modules_but_cannot_modify_them(): void
    {
        $staff = $this->makeStaffUser('staff-reference-viewer');

        $this->actingAs($staff)
            ->getJson('/api/employees')
            ->assertOk();

        $this->actingAs($staff)
            ->getJson('/api/responsibility-centers')
            ->assertOk();

        $this->actingAs($staff)
            ->getJson('/api/signatories')
            ->assertOk();

        $this->actingAs($staff)
            ->postJson('/api/employees', [])
            ->assertForbidden();

        $this->actingAs($staff)
            ->postJson('/api/responsibility-centers', [])
            ->assertForbidden();

        $this->actingAs($staff)
            ->postJson('/api/signatories', [])
            ->assertForbidden();
    }

    public function test_admin_can_access_certifications_calendar_and_swa_pages(): void
    {
        $admin = $this->makeAdminUser();

        $this->actingAs($admin)
            ->get('/certifications')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/calendar')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/swa')
            ->assertOk();
    }

    public function test_staff_cannot_access_certifications_calendar_and_swa_pages_by_default(): void
    {
        $staff = $this->makeStaffUser('staff-blank-modules');

        $this->actingAs($staff)
            ->get('/certifications')
            ->assertForbidden();

        $this->actingAs($staff)
            ->get('/calendar')
            ->assertForbidden();

        $this->actingAs($staff)
            ->get('/swa')
            ->assertForbidden();
    }

    public function test_admin_can_create_and_list_calendar_events(): void
    {
        $admin = $this->makeAdminUser();

        $this->actingAs($admin)
            ->postJson('/api/calendar', [
                'event_date' => '2026-07-01',
                'title' => 'City Charter Day',
                'event_type' => 'local_holiday',
                'description' => 'Declared local holiday for city charter celebration.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'City Charter Day')
            ->assertJsonPath('data.event_type', 'local_holiday');

        $this->actingAs($admin)
            ->getJson('/api/calendar?search=Charter')
            ->assertOk()
            ->assertJsonPath('filtered_total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'City Charter Day');

        $savedEvent = CalendarEvent::query()->sole();

        $this->assertSame('City Charter Day', $savedEvent->title);
        $this->assertSame('local_holiday', $savedEvent->event_type);
        $this->assertSame('2026-07-01', $savedEvent->event_date?->toDateString());
    }

    public function test_admin_can_create_and_list_non_ros_certification_records(): void
    {
        $admin = $this->makeAdminUser();

        $officeHead = Signatory::query()->create([
            'part' => 'A',
            'name' => 'Juan Dela Cruz',
            'office' => 'Office of the Governor',
            'title' => ['Administrative Officer V'],
        ]);

        $this->actingAs($admin)
            ->postJson('/api/certifications/non-ros', [
                'subject_name' => 'Maria Santos',
                'subject_honorific' => 'Ms.',
                'designation' => 'Administrative Aide VI',
                'office' => 'Human Resource Management Office',
                'issued_date' => '2026-04-24',
                'office_head_id' => $officeHead->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.subject_name', 'Maria Santos')
            ->assertJsonPath('data.subject_honorific', 'Ms.')
            ->assertJsonPath('data.signatory_name', 'Juan Dela Cruz');

        $this->assertDatabaseHas('certifications', [
            'certification_type' => 'non_ros',
            'subject_name' => 'Maria Santos',
            'subject_honorific' => 'Ms.',
            'designation' => 'Administrative Aide VI',
            'office' => 'Human Resource Management Office',
        ]);

        $this->actingAs($admin)
            ->getJson('/api/certifications/non-ros')
            ->assertOk()
            ->assertJsonPath('filtered_total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject_name', 'Maria Santos')
            ->assertJsonPath('data.0.subject_honorific', 'Ms.')
            ->assertJsonPath('office_heads.0.name', 'Juan Dela Cruz')
            ->assertJsonPath('office_heads.0.title', 'Administrative Officer V');

        $this->assertSame(1, Certification::query()->count());
    }

    public function test_admin_can_create_update_and_delete_users_with_roles(): void
    {
        $admin = $this->makeAdminUser();

        $createResponse = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Access Clerk',
            'username' => 'access-clerk',
            'email' => 'clerk@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role_names' => ['staff'],
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.username', 'access-clerk');

        $user = User::query()->where('username', 'access-clerk')->firstOrFail();

        $this->assertTrue($user->hasRole('staff'));

        $updateResponse = $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => 'Access Clerk Updated',
            'username' => 'access-clerk',
            'email' => 'clerk.updated@example.com',
            'role_names' => ['admin'],
        ]);

        $updateResponse
            ->assertOk()
            ->assertJsonPath('data.name', 'Access Clerk Updated');

        $user->refresh();

        $this->assertSame('clerk.updated@example.com', $user->email);
        $this->assertTrue($user->hasRole('admin'));

        $this->actingAs($admin)
            ->deleteJson("/api/users/{$user->id}")
            ->assertOk();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_cannot_delete_their_current_account(): void
    {
        $admin = $this->makeAdminUser();

        $this->actingAs($admin)
            ->deleteJson("/api/users/{$admin->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'You cannot delete your current account.');
    }

    public function test_admin_can_create_update_and_delete_roles(): void
    {
        $admin = $this->makeAdminUser();

        $createResponse = $this->actingAs($admin)->postJson('/api/roles', [
            'name' => 'finance_reviewer',
            'permissions' => ['users.view', 'roles.view'],
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'finance_reviewer');

        $role = Role::query()->where('name', 'finance_reviewer')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('roles.view'));

        $updateResponse = $this->actingAs($admin)->putJson("/api/roles/{$role->id}", [
            'name' => 'finance_reviewer_lead',
            'permissions' => ['users.view', 'users.manage'],
        ]);

        $updateResponse
            ->assertOk()
            ->assertJsonPath('data.name', 'finance_reviewer_lead');

        $role->refresh();

        $this->assertSame('finance_reviewer_lead', $role->name);
        $this->assertTrue($role->hasPermissionTo('users.manage'));
        $this->assertFalse($role->hasPermissionTo('roles.view'));

        $this->actingAs($admin)
            ->deleteJson("/api/roles/{$role->id}")
            ->assertOk();

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_assigned_roles_cannot_be_deleted(): void
    {
        $admin = $this->makeAdminUser();

        $this->actingAs($admin)->postJson('/api/roles', [
            'name' => 'assigned_role',
            'permissions' => ['users.view'],
        ])->assertCreated();

        $assignedRole = Role::query()->where('name', 'assigned_role')->firstOrFail();

        $user = User::factory()->createOne([
            'username' => 'assigned-staff',
        ]);
        $user->assignRole($assignedRole);

        $this->actingAs($admin)
            ->deleteJson("/api/roles/{$assignedRole->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Remove this role from assigned users before deleting it.');
    }

    public function test_system_roles_cannot_be_renamed_or_deleted(): void
    {
        $admin = $this->makeAdminUser();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->putJson("/api/roles/{$adminRole->id}", [
                'name' => 'admin-renamed',
                'permissions' => ['roles.view'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'System roles cannot be renamed or deleted.');

        $this->actingAs($admin)
            ->deleteJson("/api/roles/{$adminRole->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'System roles cannot be renamed or deleted.');
    }

    private function makeAdminUser(): User
    {
        $user = User::factory()->createOne([
            'username' => fake()->unique()->userName(),
        ]);
        $user->assignRole('admin');

        return $user;
    }

    private function makeStaffUser(string $username): User
    {
        $user = User::factory()->createOne([
            'username' => $username,
        ]);
        $user->assignRole('staff');

        return $user;
    }
}
