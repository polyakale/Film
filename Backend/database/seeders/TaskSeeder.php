<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $taskData = [
            ['id' => 1, 'filmId' => 1,'personId' => 1,'roleId' => 1],
        ];
    
        if (Task::count() === 0) {
            Task::factory()->createMany($taskData);
        }
    }
}
