<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@pacific.store',
            'password' => Hash::make('123456'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Seller User',
            'email' => 'seller@pacific.store',
            'password' => Hash::make('123456'),
            'role' => 'seller',
        ]);

        User::create([
            'name' => 'Customer User',
            'email' => 'customer@pacific.store',
            'password' => Hash::make('123456'),
            'role' => 'customer',
        ]);
    }
}
