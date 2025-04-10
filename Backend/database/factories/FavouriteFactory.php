<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\User;
use App\Models\Favourite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class FavouriteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Favourite::class; // Define the model property

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create('en_US'); // Or use your desired locale

        // Ensure we have at least one user and one film. Create if none exist.
        // Note: Calling factory()->create() inside a factory definition can sometimes lead to unexpected loops
        // if not handled carefully, especially during large seeding operations.
        // It's often better to ensure users/films are seeded *before* favourites.
        $userExists = User::exists();
        $filmExists = Film::exists();

        if (!$userExists) {
            User::factory()->create();
        }
        if (!$filmExists) {
            Film::factory()->create();
        }

        // --- Unique User-Film Pair Logic ---
        // This logic can be resource-intensive for large datasets. Consider optimizing if needed.

        // Retrieve all existing user-film combinations ONLY ONCE if possible, or use database constraints.
        // Using a static property to cache might help within a single seeder run, but be careful with state.
        static $existingPairsSet = null;
        if ($existingPairsSet === null) {
            $existingPairsSet = Favourite::selectRaw("CONCAT(userId, '-', filmId) as pair")
                ->pluck('pair')
                ->flip(); // Use flip for faster lookups (O(1) average)
        }

        $userIds = User::pluck('id')->all();
        $filmIds = Film::pluck('id')->all();

        // Check if users or films are empty, handle appropriately
        if (empty($userIds) || empty($filmIds)) {
            // Throw an exception or handle the case where prerequisite data is missing
            throw new \LogicException("Cannot create favourites without existing Users and Films.");
        }

        $selectedUserId = null;
        $selectedFilmId = null;
        $maxAttempts = 100; // Prevent infinite loops if all combinations are somehow exhausted
        $attempts = 0;

        do {
            $potentialUserId = $faker->randomElement($userIds);
            $potentialFilmId = $faker->randomElement($filmIds);
            $pairKey = $potentialUserId . '-' . $potentialFilmId;

            if (!isset($existingPairsSet[$pairKey])) {
                $selectedUserId = $potentialUserId;
                $selectedFilmId = $potentialFilmId;
                $existingPairsSet[$pairKey] = true; // Add the newly selected pair to the set
                break;
            }
            $attempts++;
        } while ($attempts < $maxAttempts);

        // Handle case where no unique pair could be found after reasonable attempts
        if ($selectedUserId === null || $selectedFilmId === null) {
            // Option 1: Throw an exception
            // throw new \RuntimeException("Could not find a unique User-Film pair for Favourite after {$maxAttempts} attempts.");
            // Option 2: Log a warning and potentially skip creation or use a fallback (less ideal if uniqueness is critical)
            \Log::warning("Could not find a unique User-Film pair for Favourite after {$maxAttempts} attempts. Skipping or using fallback.");
            // Fallback (use with caution, might still violate unique constraints if retried)
            $selectedUserId = $userIds[0];
            $selectedFilmId = $filmIds[0];
            // Or simply return an empty array or skip: return [];
        }
        // --- End Unique Pair Logic ---


        // Generate a random created_at datetime between 2015-01-01 and now, explicitly in UTC.
        // This avoids issues with local timezone DST transitions.
        $createdAt = $faker->dateTimeBetween('2015-01-01', 'now', 'UTC');

        // Format dates as strings in the standard 'Y-m-d H:i:s' format.
        $formattedTimestamp = $createdAt->format('Y-m-d H:i:s');

        // Generate evaluation score (e.g., 0.5 to 5.0 in 0.5 increments)
        $evaluation = $faker->numberBetween(1, 10) / 2.0;

        // Generate review content based on the evaluation.
        $content = $this->generateReviewContent($faker, $evaluation);

        return [
            'userId' => $selectedUserId,
            'filmId' => $selectedFilmId,
            'evaluation' => $evaluation,
            'content' => $content,
            // Use the formatted UTC timestamp
            'created_at' => $formattedTimestamp,
            'updated_at' => $formattedTimestamp, // Typically, updated_at matches created_at on creation
        ];
    }

    /**
     * Generate a review content string based on a random sentiment.
     *
     * @param \Faker\Generator $faker
     * @param float $evaluation
     * @return string
     */
    protected function generateReviewContent($faker, float $evaluation): string
    {
        // Expanded vocabulary with genre-specific terms
        $genres = ['drama', 'comedy', 'horror', 'romance', 'historical', 'documentary', 'action', 'sci-fi', 'thriller', 'fantasy'];
        $technicalAspects = [
            'cinematography' => ['stunning', 'gritty', 'intimate', 'sweeping', 'innovative'],
            'editing' => ['tight', 'choppy', 'rhythmic', 'seamless', 'jarring'],
            'sound design' => ['immersive', 'distracting', 'nuanced', 'powerful', 'minimalist'],
            'score' => ['haunting', 'uplifting', 'forgettable', 'epic', 'subtle'],
            'production design' => ['lavish', 'sparse', 'authentic', 'imaginative', 'drab'],
            'lighting' => ['atmospheric', 'harsh', 'subtle', 'dramatic', 'naturalistic'],
        ];
        $plotElements = ['pacing', 'plot twists', 'dialogue', 'ending', 'premise', 'world-building'];
        $characterElements = ['acting', 'character arcs', 'motivations', 'relationships', 'protagonist', 'antagonist'];

        // Determine sentiment based on evaluation
        $sentimentAdjective = match (true) {
            $evaluation >= 4.5 => $faker->randomElement(['masterful', 'brilliant', 'outstanding', 'unforgettable', 'superb']),
            $evaluation >= 4.0 => $faker->randomElement(['excellent', 'compelling', 'very good', 'highly engaging', 'impressive']),
            $evaluation >= 3.0 => $faker->randomElement(['solid', 'decent', 'interesting', 'watchable', 'uneven', 'serviceable']),
            $evaluation >= 2.0 => $faker->randomElement(['mediocre', 'disappointing', 'flawed', 'predictable', 'lackluster']),
            default => $faker->randomElement(['terrible', 'awful', 'frustrating', 'confusing', 'a waste of time', 'poor']),
        };

        $positiveConnector = $faker->randomElement(['especially the', 'highlighted by the', 'particularly the', 'with standout']);
        $negativeConnector = $faker->randomElement(['undermined by the', 'let down by the', 'despite the', 'marred by the']);
        $neutralConnector = $faker->randomElement(['regarding the', 'in terms of the', 'considering the', 'as for the']);

        // More varied sentence structures
        $structures = [
            // Structure 1: Overall impression + specific element
            fn() => sprintf(
                "Overall, a %s film. I felt the %s was %s.",
                $sentimentAdjective,
                $faker->randomElement(array_merge(array_keys($technicalAspects), $plotElements, $characterElements)),
                $faker->randomElement(['particularly strong', 'a bit weak', 'quite effective', 'somewhat lacking', $sentimentAdjective])
            ),
            // Structure 2: Technical aspect focus
            fn() => sprintf(
                "The %s was %s, %s %s %s.",
                $faker->randomElement(array_keys($technicalAspects)),
                $faker->randomElement($technicalAspects[$faker->randomElement(array_keys($technicalAspects))]), // Random adjective for the aspect
                ($evaluation >= 3.0) ? $positiveConnector : $negativeConnector,
                $faker->randomElement(array_merge(array_keys($technicalAspects), $plotElements)),
                ($evaluation >= 3.0) ? $faker->randomElement(['excellent', 'well-executed']) : $faker->randomElement(['problematic', 'poorly handled'])
            ),
            // Structure 3: Character/Plot focus
            fn() => sprintf(
                "%s %s %s, although the %s could have been %s.",
                ucfirst($faker->randomElement($characterElements)), // e.g., Acting
                ($evaluation >= 3.5) ? 'was generally' : 'felt rather',
                $sentimentAdjective,
                $faker->randomElement($plotElements), // e.g., pacing
                ($evaluation >= 3.5) ? $faker->randomElement(['tighter', 'more developed', 'clearer']) : $faker->randomElement(['more engaging', 'less predictable', 'stronger'])
            ),
            // Structure 4: Genre perspective
            fn() => sprintf(
                "For a %s movie, it was %s. The %s %s.",
                $faker->randomElement($genres),
                $sentimentAdjective,
                $faker->randomElement(['story', 'visuals', 'performances']),
                match (true) {
                    $evaluation >= 4.0 => $faker->randomElement(['really delivered', 'hit all the right notes', 'exceeded expectations']),
                    $evaluation >= 2.5 => $faker->randomElement(['had its moments', 'was a mixed bag', 'met expectations']),
                    default => $faker->randomElement(['fell flat', 'missed the mark', 'was underwhelming']),
                }
            ),
        ];

        // Build the base review using a random structure
        $review = $faker->randomElement($structures)();

        // Add optional comparison (25% chance)
        if ($faker->boolean(25)) {
            $review .= " " . $faker->randomElement([
                "Reminded me a bit of [Similar Film Name].", // Placeholder, ideally link to another film if possible
                "Doesn't quite reach the heights of [Director's Previous Work].",
                "A refreshing take compared to other recent releases in the genre.",
            ]);
        }

        // Add optional concluding remark (40% chance)
        if ($faker->boolean(40)) {
            $review .= " " . $faker->randomElement([
                ($evaluation >= 4.0) ? "Highly recommended." : "Worth seeing if you're a fan of the genre.",
                ($evaluation >= 3.0) ? "An enjoyable watch." : "Might be worth skipping.",
                "It definitely provoked some thought.",
                ($evaluation < 2.0) ? "I wouldn't watch it again." : "I might revisit this one later.",
                "Made me feel " . $faker->randomElement(['nostalgic', 'inspired', 'thoughtful', 'confused', 'annoyed']) . "."
            ]);
        }

        // Add rating reference (50% chance), ensuring it fits grammatically
        if ($faker->boolean(50)) {
            // Ensure the review doesn't end with a period before adding the rating.
            $review = rtrim($review, '.');
            $ratingStyle = $faker->randomElement([
                ", giving it a {$evaluation}/5.",
                ". I'd rate it {$evaluation} stars.",
                " - solid {$evaluation}/5.",
                ", so {$evaluation} out of 5 feels right."
            ]);
            $review .= $ratingStyle;
        }

        // Ensure the first letter is capitalized and clean up potential double spaces or leading/trailing whitespace.
        return ucfirst(trim(preg_replace('/\s+/', ' ', $review)));
    }
}
