<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Film>
 */
class FilmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Film::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3), // Ensure title is always set
            'production' => $this->faker->year(),
            'length' => $this->faker->numberBetween(60, 180),
            'presentation' => $this->faker->date(),
            'imdbLink' => $this->faker->url(),
        ];
    }
}
