<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name','owner')->first();
        $spvRole   = Role::where('name','supervisor')->first();
        $kasirRole = Role::where('name','kasir')->first();

        // OWNER
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@pos.test',
            'password' => Hash::make('password'),
            'role' => 'owner'
        ]);
        $owner->roles()->attach($ownerRole);

        // SUPERVISOR
        $spv = User::create([
            'name' => 'Supervisor',
            'email' => 'spv@pos.test',
            'password' => Hash::make('password'),
            'role' => 'supervisor'
        ]);
        $spv->roles()->attach($spvRole);

        // KASIR
        $kasir = User::create([
            'name' => 'Kasir',
            'email' => 'kasir@pos.test',
            'password' => Hash::make('password'),
            'role' => 'kasir'
        ]);
        $kasir->roles()->attach($kasirRole);
    }
}