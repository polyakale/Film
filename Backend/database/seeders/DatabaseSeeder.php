<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // User::updateOrCreate(
        //     ['email' => 'test@example.com'],
        //     ['name' => 'Test User']
        // );
        DB::statement('DELETE FROM tasks');
        DB::statement('DELETE FROM people');
        DB::statement('DELETE FROM videos');
        DB::statement('DELETE FROM films');

        $this->call([
            PositionSeeder::class,
            UserSeeder::class,
            FilmSeeder::class,
            VideoSeeder::class,
            FavouriteSeeder::class,
            RolesSeeder::class,
            PersonSeeder::class,
            TaskSeeder::class,
            // ... (más seederek)
        ]);
    }
}
