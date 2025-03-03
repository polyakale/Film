<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Person;
use App\Models\Film;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('sources/tasks.csv');
        $data = [];
        $people = Person::pluck('id')->toArray();  // Get all valid person IDs
        $films = Film::pluck('id')->toArray();  // Get all valid filmIds

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            fgetcsv($handle, 10000, ";");
            while (($row = fgetcsv($handle, 10000, ";")) !== FALSE) {
                if (count($row) >= 4) {
                    $filmId = (int) $row[1];
                    $personId = (int) $row[2];
                    // Ensure that the filmId exists in the films table
                    if (in_array($filmId, $films) && in_array($personId, $people)) {
                        $data[] = [
                            'id' => $row[0],
                            'filmId' => $filmId,
                            'personId' => $personId,
                            'roleId' => (int) $row[3],
                        ];
                    }
                }
            }
            fclose($handle);
        }

        if (Task::count() === 0 && !empty($data)) {
            Task::insert($data);
        }
    }
}
