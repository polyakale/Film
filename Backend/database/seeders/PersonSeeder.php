<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{

    public function run(): void
    {
        $filePath = database_path('sources/people.csv');
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ";");
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data[] = [
                    'id' => $row[0],
                    'name' => $row[1],
                    'gender' => (int) $row[2],
                    'photo' => $row[3],
                    'imdbLink' => $row[4],
                ];
            }
            fclose($handle);
        }

        if (Person::count() === 0) {
            Person::factory()->createMany($data);
        }
    }
}
