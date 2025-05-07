<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login(): void
    {
        $name = 'Admin2';
        $email = 'admin2@example.com';
        $positionId = 1;
        $password = 'admin123';

        User::factory()->create([
            'name' => $name,
            'email' => $email,
            'positionId' => $positionId,
            'password' => bcrypt($password),
        ]);

        $response = $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->postJson('/api/users/login', [
                'email' => $email,
                'password' => $password,
            ]);

        $response->assertStatus(200);

        $json = $response->json();
        $token = $json['data']['token'] ?? $json['token'] ?? null;
        $this->assertNotNull($token, "Token not found in login response. Response: " . json_encode($json));

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->get('/api/users');

        $response->assertStatus(200);
    }

    private function login(string $email = "testuser@example.com", string $password = "test123"): string
    {
        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test User for Login Helper',
                'password' => bcrypt($password),
                'positionId' => 2,
            ]
        );

        $response = $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->postJson('/api/users/login', [
                'email' => $email,
                'password' => $password,
            ]);

        $response->assertStatus(200);
        $json = $response->json();
        $token = $json['data']['token'] ?? $json['token'] ?? null;
        if (!$token) {
            dump($json);
            $this->fail('Token not found in login response during login helper.');
        }
        return $token;
    }

    public function test_people_create(): void
    {
        $adminUser = User::factory()->create([
            'email' => 'admin_people_test@example.com',
            'password' => bcrypt('adminpass123'),
            'positionId' => 1,
        ]);
        $token = $this->login($adminUser->email, 'adminpass123');

        $name = 'Kutya Ágnes';
        $gender = 0;
        $photo = 'dogWithHumanHair.png';

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/people', [
                    "name" => $name,
                    "gender" => $gender,
                    "photo" => $photo,
                ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('people', [
            'name' => $name,
            'gender' => $gender,
            'photo' => $photo,
        ]);
        $person = Person::where('name', $name)->first();
        $this->assertNotNull($person);
    }

    public function test_user_crud_flow(): void
    {
        $admin = User::factory()->create([
            'email' => 'crud_admin@example.com',
            'positionId' => 1,
            'password' => bcrypt('admin123'),
        ]);

        $token = $this->login($admin->email, 'admin123');

        // CREATE
        $createResponse = $this->withToken($token)->postJson('/api/users', [
            'name' => 'Test User CRUD',
            'email' => 'newuser_crud@example.com',
            'password' => 'password123',
            'positionId' => 2,
        ]);
        $createResponse->assertStatus(201);

        // Adjusted JSON structure expected: data.user.id, data.user.name, data.user.email, data.token
        $createResponse->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token'
            ],
        ]);
        $jsonResponse = $createResponse->json();
        $userId = $jsonResponse['data']['user']['id'] ?? null;
        $this->assertNotNull($userId, 'User ID was null after creation.');

        // READ (index)
        $this->withToken($token)->get('/api/users')->assertStatus(200);

        // READ (show)
        $this->withToken($token)->get("/api/users/{$userId}")->assertStatus(200);

        // UPDATE
        $this->withToken($token)->patchJson("/api/users/{$userId}", [
            'name' => 'Updated User CRUD',
        ])->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated User CRUD']);

        // DELETE
        $this->withToken($token)->delete("/api/users/{$userId}")->assertStatus(200);
    }

    public function test_film_crud_flow(): void
    {
        $admin = User::factory()->create([
            'email' => 'film_crud_admin@example.com',
            'positionId' => 1,
            'password' => bcrypt('admin123'),
        ]);
        $token = $this->login($admin->email, 'admin123');

        // CREATE - include all required fields
        $createResponse = $this->withToken($token)->postJson('/api/films', [
            'title' => 'Test Movie CRUD',
            'production' => 1934,
            'length' => 120,
            'presentation' => '1934-01-01',
            'imdbLink' => 'https://www.imdb.com/title/tt1234567',
        ]);
        // Accepting 200 since your API returns 200 on film creation
        $createResponse->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'title']]);
        $filmId = $createResponse->json('data.id');
        $this->assertNotNull($filmId, "Film ID not found in response. Response: " . $createResponse->getContent());

        // READ (show specific film)
        $this->withToken($token)->get("/api/films/{$filmId}")->assertStatus(200);

        // UPDATE
        $this->withToken($token)->patchJson("/api/films/{$filmId}", [
            'title' => 'Updated Movie Title CRUD',
        ])->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Movie Title CRUD']);

        // DELETE
        $this->withToken($token)->delete("/api/films/{$filmId}")->assertStatus(200);
    }

    public function test_user_create_validation_fails(): void
    {
        $admin = User::factory()->create([
            'email' => 'validation_admin@example.com',
            'positionId' => 1,
            'password' => bcrypt('admin123'),
        ]);
        $token = $this->login($admin->email, 'admin123');

        $response = $this->withToken($token)->postJson('/api/users', [
            'name' => '',
            'email' => 'notanemail',
            'password' => '',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
