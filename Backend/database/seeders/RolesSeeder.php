<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data=[
            ['id' => 1, 'role' => 'Actor'],
            ['id' => 2, 'role' => 'Cameraman'],
            ['id' => 3, 'role' => 'Director'],
            ['id' => 4, 'role' => 'Screenwriter'],
        ];
    
        if (Role::count() === 0) {
            Role::factory()->createMany($data);

        }
    }
}
