<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or update Default Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@dds-manager.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Create or update a Front Desk / Staff user with basic workflow module access
        $staffUser = User::updateOrCreate(
            ['email' => 'staff@dds-manager.local'],
            [
                'name' => 'Front Desk Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $staffUser->syncModules([
            'dashboard',
            'calendar',
            'front-office',
            'hygiene-recall',
            'patients',
            'eod',
            'huddle',
        ]);

        // 3. Create or update a Doctor / Provider user with clinical module access
        $doctorUser = User::updateOrCreate(
            ['email' => 'doctor@dds-manager.local'],
            [
                'name' => 'Dr. Alex Mercer',
                'password' => Hash::make('password'),
                'role' => 'provider',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $doctorUser->syncModules([
            'dashboard',
            'patients',
            'provider-portal',
            'kpis',
            'tx-miner',
        ]);
    }
}
