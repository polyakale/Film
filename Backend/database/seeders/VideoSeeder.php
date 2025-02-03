<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{

    public function run(): void
    {
        // $filePath = database_path('csv/videok.csv');
        // $data = [];
        // if (($handle = fopen($filePath, "r")) !== FALSE) {
        //     while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        //         $data[] = [
        //             'id' => $row[0],
        //             'filmId' => $row[1],
        //             'link' => $row[2],
        //             'embedLink' => $row[3],
        //         ];
        //     }
        //     fclose($handle);
        // }
        $videoData = [
            ['id' => 1, 'filmId' => 5, 'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'embedLink'=> 'élklké'],
        ];
    
        if (Video::count() === 0) {
            Video::factory()->createMany($videoData);
        }
    }
}
