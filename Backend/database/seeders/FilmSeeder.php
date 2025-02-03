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
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $presentation = $row[4];  // Assuming 'presentation' is at index 4
                
                // Handle potential formatting issues with date (e.g., '2025.02.03' -> '2025-02-03')
                try {
                    // Ensure correct format (if needed, you can adjust this to match your CSV date format)
                    $presentation = Carbon::createFromFormat('Y.m.d', $presentation)->toDateString();
                } catch (\Exception $e) {
                    // Handle parsing errors (e.g., set to null or a default date)
                    $presentation = null;
                }
                $data[] = [
                    'title' => $row[1],
                    'production' => (int) $row[2],
                    'length' => (int) $row[3],
                    'presentation' => $presentation,
                    'imdbLink' => $row[5],
                ];
            }
            fclose($handle);
        }
        // $filmData = [
        //     ['id' => 1, 'title' => 'Tree-5004','production' => 1992,'length' => 330,'presentation'=>'1994.09.11', 'imdbLink' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        // ];
    
        if (Film::count() === 0) {
            Film::factory()->createMany($data);
        }
    }
}
