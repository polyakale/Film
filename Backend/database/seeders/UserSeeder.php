<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private Collection $positions;

    public function run()
    {
        $this->createAdminUser();
        $this->createGuestUsers();
    }

    private function createAdminUser()
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'positionId' => 1,
        ]);
    }

    private function createGuestUsers()
    {
        $users = collect(range(1, 50))->map(function ($i) {
            return [
                'name' => $this->generateUniqueUsername(),
                'email' => $this->generateUniqueEmail($i),
                'password' => Hash::make('guest123'),
                'positionId' => 2,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Batch insert for better performance
        User::insert($users->all());
    }

    private function generateUniqueUsername(): string
    {
        return Str::of(fake()->userName())
            ->replaceMatches('/\d+$/', '') // Remove existing numbers
            ->append(rand(100, 999)) // Add new random numbers
            ->limit(20, '')
            ->toString();
    }

    private function generateUniqueEmail(int $index): string
    {
        return "guest{$index}@example.com";
    }
}
