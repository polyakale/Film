<?php

namespace Database\Seeders;

use App\Models\Favourite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavouriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $favouriteData=
        [
            ['id', 'userId', 'filmId', 'evaluation'],
        ];

        if(Favourite::count() === 0){
            foreach ($favouriteData as $item) {
                Favourite::create($item);
            }
        };
    }
}
