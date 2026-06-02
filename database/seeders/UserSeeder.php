<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // 1. Create the Admin User
        $admin = User::firstOrCreate([
            'name'              => 'System Administrator',
            'email'             => 'admin@gmail.com',
            'phone'             => '0911111111',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
            'is_searchable'     => false,
            'is_active'         => true,
        ]);
        $admin->assignRole('admin');

        // 2. Create the Facility Owner User
        $owner = User::firstOrCreate([
            'name'              => 'Facility Owner',
            'email'             => 'owner@gmail.com',
            'phone'             => '0922222222',
            'password'          => Hash::make('password'),
            'role'              => 'owner', // Now perfectly aligned with Spatie role name
            'email_verified_at' => now(),
            'is_searchable'     => true,
            'is_active'         => true,
        ]);
        $owner->assignRole('owner');

        // 3. Create the Regular Customer User
        $customer = User::firstOrCreate([
            'name'              => 'Regular Customer',
            'email'             => 'customer@gmail.com',
            'phone'             => '0933333333',
            'password'          => Hash::make('password'),
            'role'              => 'user', // Database enum column stays 'user' for data classification
            'email_verified_at' => now(),
            'is_searchable'     => false,
            'is_active'         => true,
        ]);
        $customer->assignRole('customer'); // Spatie role name is 'customer'
    }
}
