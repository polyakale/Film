<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use App\Models\Favourite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_add_and_remove_favorite(): void
    {
        // Create a user with a known password
        $user = User::factory()->create([
            'password' => bcrypt('secret123')
        ]);

        // Log in and obtain token
        $tokenResponse = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'secret123'
        ]);
        $token = $tokenResponse->json('data.token') ?? $tokenResponse->json('token');
        $this->assertNotNull($token, "Token not found in login response");

        // Create a film record
        $film = Film::factory()->create();

        // Add to favorites using the correct field names
        $this->withToken($token)
            ->postJson('/api/favourites', ['filmId' => $film->id])
            ->assertStatus(201);

        // Assert that the favorites table now contains the entry
        $this->assertDatabaseHas('favourites', [
            'userId' => $user->id,
            'filmId' => $film->id,
        ]);

        // Remove from favorites using the composite route
        $this->withToken($token)
            ->deleteJson("/api/favourites/{$user->id}/{$film->id}")
            ->assertStatus(200);

        // Verify that the favorite record has been removed from the database
        $this->assertDatabaseMissing('favourites', [
            'userId' => $user->id,
            'filmId' => $film->id,
        ]);
    }
}
