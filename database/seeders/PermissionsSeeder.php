<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Flush permission cache before seeding
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──────────────────────────────────────────
        $permissions = [
            'employee_fund_transactions.view',
            'employee_fund_transactions.manage',
            'employee_fund_transactions.delete',
            'employee_fund_transactions.export',
            'users.view',
            'users.manage',
            'roles.view',
            'roles.manage',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // ── Roles ─────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        // Admin gets everything
        $admin->syncPermissions(Permission::all());

        // Staff gets view + manage (no delete, no user management)
        $staff->syncPermissions([
            'employee_fund_transactions.view',
            'employee_fund_transactions.manage',
            'employee_fund_transactions.export',
        ]);

        $this->command->info('Permissions and roles seeded successfully.');
    }
}
