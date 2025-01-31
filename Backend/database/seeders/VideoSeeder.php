<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videoData = [
            ['id' => 1, 'filmId' => 1, 'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'embedLink'=> 'élklké'],
        ];
    
        if (Video::count() === 0) {
            Video::factory()->createMany($videoData);
        }
    }
}
