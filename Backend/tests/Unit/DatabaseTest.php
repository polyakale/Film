<?php

namespace Tests\Unit;

use App\Models\Favourite;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;

    // Helper methods
    protected function assertColumnType(string $table, string $column, string $type)
    {
        $this->assertEquals(
            $type,
            Schema::getColumnType($table, $column),
            "Column $table.$column should be type $type"
        );
    }

    protected function assertForeignKeyExists(
        string $table,
        string $column,
        string $foreignTable,
        string $foreignColumn
    ) {
        $foreignKeys = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);
        $exists = collect($foreignKeys)->contains(function ($fk) use ($column, $foreignTable, $foreignColumn) {
            return in_array($column, $fk->getColumns()) &&
                $fk->getForeignTableName() === $foreignTable &&
                in_array($foreignColumn, $fk->getForeignColumns());
        });
        $this->assertTrue(
            $exists,
            "Foreign key $table.$column -> $foreignTable.$foreignColumn missing"
        );
    }

    // Tests
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

    public function test_positions_table_structure()
    {
        $this->assertTrue(Schema::hasTable('positions'));
        $columns = [
            'id' => 'integer',
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

    public function test_users_table_structure()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $columns = [
            'id' => 'integer',
            'positionId' => 'integer',
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

    public function test_films_table_structure()
    {
        $this->assertTrue(Schema::hasTable('films'));
        $columns = [
            'id' => 'integer',
            'title' => 'string',
            'production' => 'integer',
            'length' => 'integer',
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

    public function test_videos_table_structure()
    {
        $this->assertTrue(Schema::hasTable('videos'));
        $columns = [
            'id' => 'integer',
            'filmId' => 'integer',
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

    public function test_favourites_table_structure()
    {
        $this->assertTrue(Schema::hasTable('favourites'));
        $columns = [
            'id' => 'integer',
            'userId' => 'integer',
            'filmId' => 'integer',
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

    public function test_roles_table_structure()
    {
        $this->assertTrue(Schema::hasTable('roles'));
        $columns = [
            'id' => 'integer',
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

    public function test_people_table_structure()
    {
        $this->assertTrue(Schema::hasTable('people'));
        $columns = [
            'id' => 'integer',
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

    public function test_tasks_table_structure()
    {
        $this->assertTrue(Schema::hasTable('tasks'));
        $columns = [
            'id' => 'integer',
            'filmId' => 'integer',
            'personId' => 'integer',
            'roleId' => 'integer',
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

    public function test_users_positions_relationships()
    {
        $position = Position::factory()->create(['name' => 'vendeg']);
        $user = User::factory()->create(['positionId' => $position->id]);
        // Test using Eloquent relationship
        $freshUser = User::with('position')->find($user->id);
        $this->assertInstanceOf(Position::class, $freshUser->position);
        $this->assertEquals($position->id, $freshUser->position->id);
    }

    public function test_favourites_users_relationships()
    {
        $user = User::factory()->create();
        $favourite = Favourite::factory()->create(['userId' => $user->id]);
        // Test using Eloquent relationship
        $freshFavourite = Favourite::with('user')->find($favourite->id);
        $this->assertInstanceOf(User::class, $freshFavourite->user);
        $this->assertEquals($user->id, $freshFavourite->user->id);
    }
}