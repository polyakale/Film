<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('Sources\tasks.csv');
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Skip the header row if present
            $headerSkipped = false;

            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // Skip the first row if it's the header
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }

                // Ensure each row has the required number of columns (at least 4)
                if (count($row) >= 4) {
                    $data[] = [
                        'id' => (int) $row[0], // Ensure the id is treated as an integer
                        'filmId' => (int) $row[1],
                        'personId' => (int) $row[2],
                        'roleId' => (int) $row[3],
                        'created_at' => now(),  // Set current time for created_at
                        'updated_at' => now(),  // Set current time for updated_at
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
