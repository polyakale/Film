<?php

namespace Tests\Feature;

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

        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'positionId' => $positionId,
            'password' => $password,
        ]);

        $response = $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->postJson('/api/users/login', data: [
                'email' => $email,
                'password' => $password
            ]);

        $response->assertStatus(200);

        $token = $response->json('data')['token'];
        $this->assertNotNull($token);

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->get('/api/users');
        $response->assertStatus(200);
    }

    private function login(string $email = "testuser@example.com", string $password = "test123")
    {
        // Remove the User::factory()->create() from here since we'll create it in the test
        $response = $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->postJson('/api/users/login', [
                'email' => $email,
                'password' => $password
            ]);

        $response->assertStatus(200);

        $json = $response->json();

        if (!isset($json['data']['token'])) {
            dump($json);
            $this->fail('Token not found in login response.');
        }

        return $json['data']['token'];
    }

    public function test_people_create()
    {
        // Create a unique test user
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('test123'),
        ]);

        $token = $this->login($user->email, 'test123');
        $name = 'Kutya Ágnes';
        $gender = 0;
        $photo = 'dogWithHumanHair.png';

        $response = $this
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->postJson('/api/people', [
                "name" => $name,
                "gender" => $gender,
                "photo" => $photo
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('people', ['name' => $name]);
        $this->assertDatabaseHas('people', ['gender' => $gender]);
        $this->assertDatabaseHas('people', ['photo' => $photo]);
        $person = Person::where('name', $name)->first();
        $this->assertNotNull($person);
    }
}