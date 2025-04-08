<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\User;
use App\Models\Favourite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class FavouriteFactory extends Factory
{
    public function definition(): array
    {
        $faker = Faker::create('en_US');

        // First ensure we have at least one user and film
        $user = User::first() ?? User::factory()->create();
        $film = Film::first() ?? Film::factory()->create();

        // Get all possible combinations that don't exist yet
        $existingPairs = Favourite::select(['userId', 'filmId'])
            ->get()
            ->map(fn($item) => $item->userId . '-' . $item->filmId)
            ->all();

        $userIds = User::pluck('id')->all();
        $filmIds = Film::pluck('id')->all();

        $possibleCombinations = collect($userIds)
            ->crossJoin($filmIds)
            ->map(fn($pair) => $pair[0] . '-' . $pair[1])
            ->diff($existingPairs)
            ->map(fn($str) => explode('-', $str))
            ->all();

        if (empty($possibleCombinations)) {
            // Fallback to existing user and film if no combinations left
            $combination = [$user->id, $film->id];
        } else {
            $combination = $faker->randomElement($possibleCombinations);
        }

        $evaluation = $faker->numberBetween(1, 10) / 2;

        // Review content generation remains the same
        $content = $this->generateReviewContent($faker, $evaluation);

        return [
            'userId' => $combination[0],
            'filmId' => $combination[1],
            'evaluation' => $evaluation,
            'content' => $content,
        ];
    }

    protected function generateReviewContent($faker, $evaluation): string
    {
        // More automation: Dynamically generated review sentences
        $openers = [
            "I just watched this and",
            "Honestly,",
            "To be fair,",
            "I had high hopes, but",
            "What a surprise!",
            "After watching,"
        ];

        $middleParts = [
            "the storyline was",
            "the acting felt",
            "the cinematography was",
            "the pacing was",
            "the dialogue seemed",
            "the soundtrack was"
        ];

        $positives = [
            "absolutely brilliant!",
            "incredibly well done.",
            "captivating from start to finish.",
            "a masterpiece in every way.",
            "one of the best I've seen recently."
        ];

        $neutrals = [
            "just okay, nothing special.",
            "pretty average.",
            "good in some parts, but weak in others.",
            "not bad, but not outstanding.",
            "a mix of highs and lows."
        ];

        $negatives = [
            "disappointing and dull.",
            "not engaging at all.",
            "poorly executed.",
            "a total letdown.",
            "frustratingly bad."
        ];

        $closers = [
            "Would I watch it again? Maybe.",
            "Overall, it was an interesting experience.",
            "It had its moments, but could’ve been better.",
            "Not sure I'd recommend it.",
            "I'd tell others to check it out, but with lower expectations."
        ];

        // Determine which phrases to use based on evaluation score
        $sentiment = match (true) {
            $evaluation >= 4.5 => $faker->randomElement($positives),
            $evaluation >= 3.0 => $faker->randomElement($neutrals),
            default => $faker->randomElement($negatives),
        };

        return sprintf(
            "%s %s %s %s",
            $faker->randomElement($openers),
            $faker->randomElement($middleParts),
            $sentiment,
            $faker->randomElement($closers)
        );
    }
}
