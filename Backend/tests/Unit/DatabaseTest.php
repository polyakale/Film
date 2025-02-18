<?php

namespace Tests\Unit;

use App\Models\Favourite;
use App\Models\Film;
use App\Models\Person;
use App\Models\Position;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;
    // Helper methods
    protected function assertColumnType(string $table, string $column, string $type)
    {
        $actualType = Schema::getColumnType($table, $column);
        $expectedType = ($type === 'string') ? 'varchar' : $type; // Map 'string' to 'varchar'
        // Handle MySQL's 'tinyint' for boolean columns
        if ($type === 'boolean' && $actualType === 'tinyint') {
            $actualType = 'boolean';
        }
        $this->assertEquals(
            $expectedType,
            $actualType,
            "Column $table.$column should be type $expectedType (got $actualType)"
        );
    }
    protected function assertForeignKeyExists(
        string $table,
        string $column,
        string $foreignTable,
        string $foreignColumn
    ) {
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, 
                REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE
            WHERE 
                TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [config('database.connections.mysql.database'), $table, $column]);

        $exists = collect($foreignKeys)->contains(function ($fk) use ($foreignTable, $foreignColumn) {
            return $fk->REFERENCED_TABLE_NAME === $foreignTable &&
                $fk->REFERENCED_COLUMN_NAME === $foreignColumn;
        });

        $this->assertTrue(
            $exists,
            "Foreign key $table.$column -> $foreignTable.$foreignColumn missing"
        );
    }
    
    // Testing database creation and if all tables exist
    public function test_database_creation_and_tables_exist()
    {
        $databaseName = config('database.connections.' . config('database.default') . '.database');

        $this->assertEquals(
            env('DB_DATABASE'),
            $databaseName,
            'Database name mismatch'
        );
        $tables = [
            'positions',
            'users',
            'films',
            'videos',
            'favourites',
            'roles',
            'people',
            'tasks'
        ];
        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table $table does not exist"
            );
        }
    }

    // Positions table structure testing
    public function test_positions_table_structure()
    {
        $this->assertTrue(Schema::hasTable('positions'));

        $columns = [
            'id' => 'int',
            'name' => 'string',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('positions', $column),
                "Positions table missing $column column"
            );
            $this->assertColumnType('positions', $column, $type);
        }
        $this->assertTrue(
            Schema::hasIndex('positions', ['id']),
            'Primary key index missing on positions'
        );
    }

    // Users table structure testing
    public function test_users_table_structure()
    {
        $this->assertTrue(Schema::hasTable('users'));

        $columns = [
            'id' => 'int',
            'positionId' => 'int',
            'name' => 'string',
            'email' => 'string',
            'password' => 'string',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Users table missing $column column"
            );
            $this->assertColumnType('users', $column, $type);
        }
        $this->assertForeignKeyExists('users', 'positionId', 'positions', 'id');
    }

    // Films table structure testing
    public function test_films_table_structure()
    {
        $this->assertTrue(Schema::hasTable('films'));

        $columns = [
            'id' => 'int',
            'title' => 'string',
            'production' => 'int',
            'length' => 'int',
            'presentation' => 'date',
            'imdbLink' => 'string',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('films', $column),
                "Films table missing $column column"
            );
            $this->assertColumnType('films', $column, $type);
        }
        $this->assertTrue(
            Schema::hasIndex('films', ['id']),
            'Primary key index missing on films'
        );
    }

    // Videos table structure testing
    public function test_videos_table_structure()
    {
        $this->assertTrue(Schema::hasTable('videos'));

        $columns = [
            'id' => 'int',
            'filmId' => 'int',
            'link' => 'string',
            'embedLink' => 'string',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('videos', $column),
                "Videos table missing $column column"
            );
            $this->assertColumnType('videos', $column, $type);
        }
        $this->assertForeignKeyExists('videos', 'filmId', 'films', 'id');
    }

    // Favourites table structure testing
    public function test_favourites_table_structure()
    {
        $this->assertTrue(Schema::hasTable('favourites'));

        $columns = [
            'id' => 'int',
            'userId' => 'int',
            'filmId' => 'int',
            'evaluation' => 'decimal',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('favourites', $column),
                "Favourites table missing $column column"
            );
            $this->assertColumnType('favourites', $column, $type);
        }
        $this->assertForeignKeyExists('favourites', 'userId', 'users', 'id');
        $this->assertForeignKeyExists('favourites', 'filmId', 'films', 'id');
    }

    // Roles table structure testing
    public function test_roles_table_structure()
    {
        $this->assertTrue(Schema::hasTable('roles'));

        $columns = [
            'id' => 'int',
            'role' => 'string',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('roles', $column),
                "Roles table missing $column column"
            );
            $this->assertColumnType('roles', $column, $type);
        }
        $this->assertTrue(
            Schema::hasIndex('roles', ['id']),
            'Primary key index missing on roles'
        );
    }

    // People table structure testing
    public function test_people_table_structure()
    {
        $this->assertTrue(Schema::hasTable('people'));

        $columns = [
            'id' => 'int',
            'name' => 'string',
            'gender' => 'boolean',
            'photo' => 'string',
            'imdbLink' => 'string',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('people', $column),
                "People table missing $column column"
            );
            $this->assertColumnType('people', $column, $type);
        }
        $this->assertTrue(
            Schema::hasIndex('people', ['id']),
            'Primary key index missing on people'
        );
    }

    // Tasks table structure testing
    public function test_tasks_table_structure()
    {
        $this->assertTrue(Schema::hasTable('tasks'));

        $columns = [
            'id' => 'int',
            'filmId' => 'int',
            'personId' => 'int',
            'roleId' => 'int',
        ];
        foreach ($columns as $column => $type) {
            $this->assertTrue(
                Schema::hasColumn('tasks', $column),
                "Tasks table missing $column column"
            );
            $this->assertColumnType('tasks', $column, $type);
        }
        $this->assertForeignKeyExists('tasks', 'filmId', 'films', 'id');
        $this->assertForeignKeyExists('tasks', 'personId', 'people', 'id');
        $this->assertForeignKeyExists('tasks', 'roleId', 'roles', 'id');
    }

    // Users ↔ Positions relationship
    public function test_users_positions_relationships()
    {
        $position = Position::factory()->create(['name' => 'vendeg']);
        $user = User::factory()->create(['positionId' => $position->id]);
        // Test using Eloquent relationship
        $freshUser = User::with('position')->find($user->id);
        $this->assertInstanceOf(Position::class, $freshUser->position);
        $this->assertEquals($position->id, $freshUser->position->id);
    }

    // Favourites ↔ Users relationship
    public function test_favourites_users_relationships()
    {
        $user = User::factory()->create();
        $favourite = Favourite::factory()->create(['userId' => $user->id]);
        // Test using Eloquent relationship
        $freshFavourite = Favourite::with('user')->find($favourite->id);
        $this->assertInstanceOf(User::class, $freshFavourite->user);
        $this->assertEquals($user->id, $freshFavourite->user->id);
    }

    // Films ↔ Videos relationship
    public function test_films_videos_relationships()
    {
        $film = Film::factory()->create();
        $video = Video::factory()->create(['filmId' => $film->id]);
        // Test film has videos
        $this->assertCount(1, $film->videos);
        $this->assertEquals($video->id, $film->videos->first()->id);
        // Test video belongs to film
        $this->assertEquals($film->id, $video->film->id);
    }

    // Films ↔ Tasks relationship
    public function test_films_tasks_relationships()
    {
        $film = Film::factory()->create();
        $task = Task::factory()->create(['filmId' => $film->id]);
        // Test film has tasks
        $this->assertCount(1, $film->tasks);
        $this->assertEquals($task->id, $film->tasks->first()->id);
        // Test task belongs to film
        $this->assertEquals($film->id, $task->film->id);
    }

    // Films ↔ Favourites relationship
    public function test_films_favourites_relationships()
    {
        $film = Film::factory()->create();
        $favourite = Favourite::factory()->create(['filmId' => $film->id]);
        // Test film has favourites
        $this->assertCount(1, $film->favourites);
        $this->assertEquals($favourite->id, $film->favourites->first()->id);
        // Test favourite belongs to film
        $this->assertEquals($film->id, $favourite->film->id);
    }

    // Tasks ↔ People relationship
    public function test_tasks_people_relationships()
    {
        $person = Person::factory()->create();
        $task = Task::factory()->create(['personId' => $person->id]);
        // Test person has tasks
        $this->assertCount(1, $person->tasks);
        $this->assertEquals($task->id, $person->tasks->first()->id);
        // Test task belongs to person
        $this->assertEquals($person->id, $task->person->id);
    }

    // Tasks ↔ Roles relationship
    public function test_tasks_roles_relationships()
    {
        $role = Role::factory()->create();
        $task = Task::factory()->create(['roleId' => $role->id]);
        // Test role has tasks
        $this->assertCount(1, $role->tasks);
        $this->assertEquals($task->id, $role->tasks->first()->id);
        // Test task belongs to role
        $this->assertEquals($role->id, $task->role->id);
    }
}
