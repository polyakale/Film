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
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'positionId' => 1, // 1 = Admin
        ]);

        User::factory()->create([
            'name' => 'DarwinWinner400',
            'email' => 'guest@example.com',
            'password' => bcrypt('guest123'),
            'positionId' => 2, // 2 = Guest
        ]);
        User::factory()->create([
            'name' => 'xXtreeEaterXx',
            'email' => 'guest1@example.com',
            'password' => bcrypt('guest123'),
            'positionId' => 2, // 2 = Guest
        ]);
        User::factory()->create([
            'name' => 'rAnd3l',
            'email' => 'guest2@example.com',
            'password' => bcrypt('guest123'),
            'positionId' => 2, // 2 = Guest
        ]);
        User::factory()->create([
            'name' => 'aDaM3000',
            'email' => 'guest3@example.com',
            'password' => bcrypt('guest123'),
            'positionId' => 2, // 2 = Guest
        ]);
        User::factory()->create([
            'name' => 'iAmSteve',
            'email' => 'guest4@example.com',
            'password' => bcrypt('guest123'),
            'positionId' => 2, // 2 = Guest
        ]);
    }
}
