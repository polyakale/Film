<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{

    public function run(): void
    {
        $personData = [
            ['id' => 1, 'name' => 'A. H.','gender' => 1,'photo' => 'AH.jpg', 'imbdLink'=>'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ];
    
        if (Person::count() === 0) {
            Person::factory()->createMany($personData);
        }
    }
}
