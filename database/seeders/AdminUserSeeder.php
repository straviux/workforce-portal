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
        if (app()->environment('production') && ! env('SEED_DEFAULT_ADMIN')) {
            $this->command->warn('Skipping default admin seeding in production (set SEED_DEFAULT_ADMIN=true to force).');

            return;
        }

        $existing = User::where('email', 'admin@workforce.local')->first();

        if ($existing) {
            $existing->syncRoles(['admin']);
            $this->command->info('Admin user already exists — left password untouched.');

            return;
        }

        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@workforce.local',
            'username' => 'admin',
            'password' => Hash::make('admin0511'),
        ]);

        $admin->syncRoles(['admin']);

        $this->command->info('Admin user created: username=admin / admin0511');
        $this->command->warn('Change the default password after first login.');
    }
}
