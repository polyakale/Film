<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Film;
use Carbon\Carbon;


class FilmSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('sources/films.csv');
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ";");
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $presentation = $row[4];  // Assuming 'presentation' is at index 4
                try {
                    // Ensure correct format (if needed, you can adjust this to match your CSV date format)
                    $presentation = Carbon::createFromFormat('Y.m.d', $presentation)->toDateString();
                } catch (\Exception $e) {
                    // Handle parsing errors (e.g., set to null or a default date)
                    $presentation = null;
                }
                $data[] = [
                    'id' => $row[0],
                    'title' => $row[1],
                    'production' => (int) $row[2],
                    'length' => (int) $row[3],
                    'presentation' => $presentation,
                    'imdbLink' => $row[5],
                ];
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
                // die;

            }
            fclose($handle);
        }

        if (Film::count() === 0) {
            Film::factory()->createMany($data);
        }
    }
}
