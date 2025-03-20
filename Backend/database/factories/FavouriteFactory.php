<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class FavouriteFactory extends Factory
{
    public function definition(): array
    {
        $faker = Faker::create('hu_HU');

        do {
            $userId = User::inRandomOrder()->first()->id;
        } while ($userId == 1);

        $filmId = Film::inRandomOrder()->first()->id;
        // Generate ratings in 0.5 increments between 1-5
        $evaluation = $faker->numberBetween(2, 10) / 2;

        return [
            'userId' => $userId,
            'filmId' => $filmId,
            'evaluation' => $evaluation
        ];
    }
}
