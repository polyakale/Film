<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $filePath = database_path('csv\film.csv');

        // // Adatok beolvasása a CSV fájlból
        // $data = [];
        // if (($handle = fopen($filePath, "r")) !== FALSE) {
        //     while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        //         $data[] = [
        //             'id' => $row[0],
        //             'osztalyNev' => $row[1],
        //         ];
        //     }
        //     fclose($handle);
        // }


        // if (Osztaly::count() === 0) {
        //     Osztaly::factory()->createMany($data);
        // }
    }
}
