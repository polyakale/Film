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

        // Ensure we have at least one user and one film.
        $user = User::first() ?? User::factory()->create();
        $film = Film::first() ?? Film::factory()->create();

        // Generate a random created_at datetime between 2015-01-01 and now.
        $createdAt = $faker->dateTimeBetween('2015-01-01', 'now');

        // Retrieve all existing user-film combinations to avoid inserting duplicates.
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
            // Fallback to an existing user and film if no new combinations remain.
            $combination = [$user->id, $film->id];
        } else {
            $combination = $faker->randomElement($possibleCombinations);
        }

        $evaluation = $faker->numberBetween(1, 10) / 2;

        // Generate review content based on the evaluation.
        $content = $this->generateReviewContent($faker, $evaluation);

        return [
            'userId'     => $combination[0],
            'filmId'     => $combination[1],
            'evaluation' => $evaluation,
            'content'    => $content,
            // Format dates as strings in the proper 'Y-m-d H:i:s' format.
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $createdAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate a review content string based on a random sentiment.
     *
     * @param \Faker\Generator $faker
     * @param float $evaluation
     * @return string
     */
    protected function generateReviewContent($faker, $evaluation): string
    {
        // Expanded vocabulary with genre-specific terms
        $genres = ['drama', 'comedy', 'horror', 'romance', 'historical', 'documentary', 'action'];
        $technicalAspects = [
            'lighting' => ['atmospheric', 'harsh', 'subtle', 'dramatic'],
            'editing' => ['tight', 'choppy', 'rhythmic', 'seamless'],
            'sound' => ['immersive', 'distracting', 'nuanced', 'powerful']
        ];

        // Sentence components
        $structures = [
            // Structure 1: Personal experience
            fn() => sprintf(
                "As someone who loves %s films, %s %s %s %s",
                $faker->randomElement($genres),
                $faker->randomElement(["I was ", "I found myself "]),
                $faker->randomElement(["completely ", "utterly ", "surprisingly "]),
                match (true) {
                    $evaluation >= 4.5 => $faker->randomElement(["captivated", "moved", "transported"]),
                    $evaluation >= 3 => $faker->randomElement(["engaged", "interested", "curious"]),
                    default => $faker->randomElement(["bored", "disappointed", "frustrated"])
                },
                $faker->randomElement([". The %s really stood out to me.", ". However, the %s could have been better."])
            ),

            // Structure 2: Technical analysis
            fn() => sprintf(
                "From a technical perspective, %s %s %s. %s %s",
                $faker->randomElement($technicalAspects['lighting']) . ' lighting',
                $faker->randomElement(["combined with", "contrasted with", "was undermined by"]),
                $faker->randomElement($technicalAspects['editing']) . ' editing',
                $faker->randomElement(["While", "Although", "Even though"]),
                $faker->randomElement(["the %s made for %s viewing experience", "the %s left me feeling %s"])
            ),

            // Structure 3: Character-driven
            fn() => sprintf(
                "%s character development was %s, %s %s %s",
                $faker->randomElement(["The", "While the main", "Secondary"]),
                match (true) {
                    $evaluation >= 4.5 => $faker->randomElement(["masterful", "nuanced", "compelling"]),
                    $evaluation >= 3 => $faker->randomElement(["uneven", "predictable", "serviceable"]),
                    default => $faker->randomElement(["non-existent", "rushed", "confusing"])
                },
                $faker->randomElement(["the", "their"]),
                $faker->randomElement(["arcs", "motivations", "relationships"]),
                $faker->randomElement(["felt %s authentic.", "left me %s."])
            )
        ];

        // Build the review
        $review = $faker->randomElement($structures)();

        // Add dynamic elements
        $review = preg_replace_callback('/%s/', function () use ($faker, $evaluation, $genres, $technicalAspects) {
            return $faker->randomElement([
                $faker->randomElement($genres),
                $faker->randomElement($technicalAspects['sound']),
                match (true) {
                    $evaluation >= 4.5 => $faker->randomElement(["rewarding", "memorable"]),
                    $evaluation >= 3 => $faker->randomElement(["mixed", "inconsistent"]),
                    default => $faker->randomElement(["regrettable", "forgettable"])
                },
                $faker->randomElement(["cinematography", "score", "production design"]),
                $faker->randomElement(["genuinely", "painfully", "surprisingly"])
            ]);
        }, $review);

        // Add optional postscript 30% of the time
        if ($faker->boolean(30)) {
            $review .= " " . $faker->randomElement([
                "This one will stay with me for days.",
                "A flawed but fascinating experiment.",
                "Not what I expected, in " . ($evaluation >= 3 ? "the best way" : "all the wrong ways") . ".",
                "Proof that " . $faker->randomElement([
                    "budget doesn't equal quality",
                    "great ideas can overcome limitations",
                    "even masters have off days"
                ]) . "."
            ]);
        }

        // Add rating reference 50% of the time
        if ($faker->boolean(50)) {
            $ratingStyle = $faker->randomElement([
                "I'd give it a solid {$evaluation}/5.",
                "{$evaluation} stars feels about right.",
                "On my personal scale: {$evaluation} out of 5."
            ]);
            $review = substr($review, 0, -1) . " " . $ratingStyle;
        }

        return ucfirst(str_replace('  ', ' ', $review));
    }
}
