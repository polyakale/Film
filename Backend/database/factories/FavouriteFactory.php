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
    $faker = Faker::create('hu_HU');
    
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
    
    return [
        'userId' => $combination[0],
        'filmId' => $combination[1],
        'evaluation' => $faker->numberBetween(1, 10) / 2
    ];
}
}