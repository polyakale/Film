<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{

    public function run(): void
    {
        // $filePath = database_path('csv/szemelyek.csv');
        // $data = [];
        // if (($handle = fopen($filePath, "r")) !== FALSE) {
        //     while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        //         $data[] = [
        //             'id' => $row[0],
        //             'name' => $row[1],
        //             'gender' => $row[2],
        //             'photo' => $row[3],
        //             'imbdLink' => $row[4],
        //         ];
        //     }
        //     fclose($handle);
        // }
        $personData = [
            ['id' => 1, 'name' => 'A. H.','gender' => 1,'photo' => 'AH.jpg', 'imbdLink'=>'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ];
    
        if (Person::count() === 0) {
            Person::factory()->createMany($personData);
        }
    }
}
