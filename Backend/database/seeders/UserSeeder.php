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
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->createMany([
                [
                    'id' => 1,
                    'name' => 'test',
                    'positionId' => '1',
                    'email' => 'test@example.com',
                    'password' => '1234567',
                ],
                [
                    'id' => 2,
                    'name' => 'testGuest1',
                    'positionId' => '2',
                    'email' => 'user1@example.com',
                    'password' => '1234567',
                ],
                [
                    'id' => 3,
                    'name' => 'testGuest2',
                    'positionId' => '2',
                    'email' => 'user2@example.com',
                    'password' => '1234567',
                ],
            ]);
        }
    }
}
