<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\User;
use App\Models\Favourite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class FavouriteFactory extends Factory
{
    public function definition(): array
    {
        $faker = Faker::create('en_US');

        static $possibleCombinations;

        if (!isset($possibleCombinations)) {
            $userIds = User::where('id', '!=', 1)->pluck('id');
            $filmIds = Film::pluck('id');

            $possibleCombinations = collect($userIds)
                ->crossJoin($filmIds)
                ->shuffle()
                ->all();

            $existingPairs = Favourite::select(['userId', 'filmId'])
                ->get()
                ->map(fn($item) => [$item->userId, $item->filmId])
                ->all();

            $possibleCombinations = array_diff($possibleCombinations, $existingPairs);
        }

        if (empty($possibleCombinations)) {
            throw new \Exception("No more unique user-film combinations available");
        }

        $combination = array_pop($possibleCombinations);
        $evaluation = $faker->numberBetween(1, 10) / 2;

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

        // Generate a more human-like review sentence
        $content = sprintf(
            "%s %s %s %s",
            $faker->randomElement($openers),
            $faker->randomElement($middleParts),
            $sentiment,
            $faker->randomElement($closers)
        );

        return [
            'userId' => $combination[0],
            'filmId' => $combination[1],
            'evaluation' => $evaluation,
            'content' => $content,
        ];
    }
}
