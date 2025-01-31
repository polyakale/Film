<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // $filePath = database_path('csv/feladatok.csv');
        // $data = [];
        // if (($handle = fopen($filePath, "r")) !== FALSE) {
        //     while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        //         $data[] = [
        //             'id' => $row[0],
        //             'filmId' => $row[1],
        //             'personId' => $row[2],
        //             'roleId' => $row[3],
        //         ];
        //     }
        //     fclose($handle);
        // }
        $taskData = [
            ['id' => 1, 'filmId' => 1,'personId' => 1,'roleId' => 1],
        ];
    
        if (Task::count() === 0) {
            Task::factory()->createMany($taskData);
        }
    }
}
