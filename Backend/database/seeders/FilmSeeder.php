<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Film;

class FilmSeeder extends Seeder
{
    public function run(): void
    {
        // $filePath = database_path('csv/filmek.csv');
        // $data = [];
        // if (($handle = fopen($filePath, "r")) !== FALSE) {
        //     while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        //         $data[] = [
        //             'id' => $row[0],
        //             'title' => $row[1],
        //             'production' => $row[2],
        //             'length' => $row[3],
        //             'presentation' => $row[4],
        //             'imbdLink' => $row[5],
        //         ];
        //     }
        //     fclose($handle);
        // }
        $filmData = [
            ['id' => 1, 'title' => 'Tree-5004','production' => 1992,'length' => 330,'presentation'=>'1994.09.11', 'imbdLink' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ];
    
        if (Film::count() === 0) {
            Film::factory()->createMany($filmData);
        }
    }
}
