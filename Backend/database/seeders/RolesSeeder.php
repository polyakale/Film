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
            ['id' => 1, 'role' => 'színész'],
            ['id' => 2, 'role' => 'operatőr'],
            ['id' => 3, 'role' => 'rendező'],
            ['id' => 4, 'role' => 'forgatókönyvíró'],
        ];
    
        if (Role::count() === 0) {
            Role::factory()->createMany($data);

        }
    }
}
