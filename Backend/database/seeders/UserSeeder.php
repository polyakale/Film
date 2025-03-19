<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/UserSeeder.php
    public function run()
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'positionId' => 1, // 1 = Admin
        ]);

        User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => bcrypt('guest123'),
            'positionId' => 2, // 2 = Guest
        ]);
    }
}
