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
        // Example data
        $favouriteData = [
            [
                'userId' => 1, // replace with a valid user ID
                'filmId' => 1, // replace with a valid film ID
                'evaluation' => 5, // example evaluation
            ],
            // Add more data as needed
        ];

        // Only seed if there are no entries yet
        if (Favourite::count() === 0) {
            foreach ($favouriteData as $item) {
                Favourite::create($item);
            }
        }
    }
}
