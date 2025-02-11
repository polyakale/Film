<?php

namespace Database\Seeders;

use App\Models\Favourite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavouriteSeeder extends Seeder
{
    public function run(): void
    {
        $favouriteData = [
            [
                'userId' => 1, // replace with a valid user ID
                'filmId' => 1, // replace with a valid film ID
                'evaluation' => 4.80, // example evaluation
            ],
        ];
        // Only seed if there are no entries yet
        if (Favourite::count() === 0) {
            foreach ($favouriteData as $item) {
                Favourite::create($item);
            }
        }
    }
}
