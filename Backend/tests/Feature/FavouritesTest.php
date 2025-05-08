<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use App\Models\Favourite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FavouritesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_add_and_remove_favourite(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $token = $this->postJson('/api/users/login', [
            'email' => $user->email,
            'password' => 'secret123'
        ])->json('data.token');

        $film = Film::factory()->create();

        // Add to favourites
        $addResponse = $this->withToken($token)
            ->postJson('/api/favourites', [
                'userId' => $user->id,
                'filmId' => $film->id,
                'evaluation' => 5,
                'content' => 'Test review'
            ]);

        $addResponse->assertStatus(201);

        $this->assertDatabaseHas('favourites', [
            'userId' => $user->id,
            'filmId' => $film->id,
        ]);

        // Remove from favourites
        $id = $addResponse['data']['id'];
        $this->withToken($token)
            ->deleteJson("/api/favourites/{$id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('favourites', [
            'userId' => $user->id,
            'filmId' => $film->id,
        ]);
    }
}
