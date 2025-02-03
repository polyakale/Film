<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('sources\tasks.csv');
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($row) >= 4) {
                    $data[] = [
                        'id' => $row[0],
                        'filmId' => $row[1],
                        'personId' => $row[2],
                        'roleId' => $row[3],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                }
            }
            fclose($handle);
        }

        if (Task::count() === 0) {
            Task::insert($data);  // Use insert instead of createMany for performance
        }
    }
}
