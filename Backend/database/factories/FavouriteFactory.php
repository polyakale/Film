<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favourite>
 */
class FavouriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create('hu_HU');
        do {
            # code...
            $userId = User::inRandomOrder()->first()->id;
        } while ($userId==1);
        $filmId = Film::inRandomOrder()->first()->id;
        $evalution = $faker->randomFloat(1,0,5);

        return [
            'userId' => $userId,
            'filmId' => $filmId,
            'evaluation' => $evalution
        ];
    }
}
