<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@workforce.local'],
            [
                'name'     => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin0511'),
            ],
        );

        $admin->syncRoles(['admin']);

        $this->command->info("Admin user ready: username=admin / admin0511");
        $this->command->warn("Change the default password after first login.");
    }
}
