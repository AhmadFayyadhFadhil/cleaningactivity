<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@cleaning.local',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Admin');

        // GA
        $ga = User::create([
            'name' => 'GA Manager',
            'email' => 'ga@cleaning.local',
            'password' => Hash::make('password'),
        ]);
        $ga->assignRole('GA');

        // Supervisor
        $supervisor = User::create([
            'name' => 'Supervisor Cleaning',
            'email' => 'supervisor@cleaning.local',
            'password' => Hash::make('password'),
        ]);
        $supervisor->assignRole('Supervisor');

        // Cleaning Service
        for ($i = 1; $i <= 5; $i++) {
            $cleaning = User::create([
                'name' => "Cleaning Service $i",
                'email' => "cleaning$i@cleaning.local",
                'password' => Hash::make('password'),
            ]);
            $cleaning->assignRole('Cleaning Service');
        }

        // PIC Area
        for ($i = 1; $i <= 3; $i++) {
            $pic = User::create([
                'name' => "PIC Area $i",
                'email' => "pic$i@cleaning.local",
                'password' => Hash::make('password'),
            ]);
            $pic->assignRole('PIC Area');
        }
    }
}