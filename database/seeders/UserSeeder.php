<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // OWNER
        User::create([
            'name' => 'Owner',
            'email' => 'owner@pos.test',
            'password' => Hash::make('password'),
            'role' => 'owner'
        ]);

        // SUPERVISOR
        User::create([
            'name' => 'Supervisor',
            'email' => 'spv@pos.test',
            'password' => Hash::make('password'),
            'role' => 'supervisor'
        ]);

        // KASIR
        User::create([
            'name' => 'Kasir',
            'email' => 'kasir@pos.test',
            'password' => Hash::make('password'),
            'role' => 'kasir'
        ]);
    }
}
