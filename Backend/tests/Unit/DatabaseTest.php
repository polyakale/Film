<?php

namespace Tests\Unit;

use App\Models\Diak;
use App\Models\Favourite;
use App\Models\Osztaly;
use App\Models\Position;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Container\Attributes\DB as AttributesDB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;


    //Adatbázis és tábláinak ellenőrzése
    public function test_database_creation_end_tables_exists()
    {
        $databaseNameConn = DB::connection()->getDatabaseName();
        // dd($databaseNameConn);
        $databaseNameEnv = env('DB_DATABASE');
        //dd($databaseNameConn == $databaseNameEnv);
        //Az adatbázis ellenőrzése
        $this->assertEquals($databaseNameConn, $databaseNameEnv);
        //Táblák létezésének 
        $this->assertDatabaseHas('positions');
        $this->assertDatabaseHas('users');
        $this->assertDatabaseHas('films');
        $this->assertDatabaseHas('videos');
        $this->assertDatabaseHas('favourites');
        $this->assertDatabaseHas('roles');
        $this->assertDatabaseHas('people');
        $this->assertDatabaseHas('tasks');
        echo PHP_EOL . "\tDatabase -> DB_DATABASE={$databaseNameEnv} | database: {$databaseNameConn}";
    }

    public function test_positions_table_structure()
    {
        $this->assertTrue(Schema::hasTable('positions'), 'Az "positions" tábla nem létezik.');

        $columns = DB::select('DESCRIBE positions');
        $this->assertContains('id', array_column($columns, 'Field'));
        $this->assertContains('name', array_column($columns, 'Field'));
        $this->assertEquals('int(11)', $columns[0]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('varchar(6)', $columns[1]->Type); // Feltételezzük, hogy az 'osztalyNev' a második
        // Elsődleges kulcs ellenőrzése
        $primaryKeys = DB::select('SHOW KEYS FROM positions WHERE Key_name = "PRIMARY"');
        $this->assertCount(1, $primaryKeys);
        $this->assertEquals('id', $primaryKeys[0]->Column_name);
    }

    public function test_users_table_structure()
    {
        // Ellenőrizzük, hogy a tábla létezik
        $this->assertTrue(Schema::hasTable('users'));
        // Ellenőrizzük a mezőket és azok típusait
        $this->assertTrue(Schema::hasColumn('users', 'id'));
        $this->assertTrue(Schema::hasColumn('users', 'name'));
        $this->assertTrue(Schema::hasColumn('users', 'positionId'));
        $this->assertEquals('int', Schema::getColumnType('users', 'id'));
        $this->assertEquals('int', Schema::getColumnType('users', 'positionId'));
        //dd(Schema::getColumnType('users', 'name'));
        $this->assertEquals('varchar', Schema::getColumnType('users', 'name'));

        $this->assertTrue(Schema::hasColumn('users', 'id'));
        $this->assertTrue(Schema::hasColumn('users', 'positionId'));
        //Elsődleges kulcs
        $indexes = DB::select('SHOW INDEX FROM users');
        $primaryIndex = collect($indexes)->firstWhere('Key_name', 'PRIMARY');
        $this->assertNotNull($primaryIndex);
    }

    public function test_films_table_structure()
    {
        $this->assertTrue(Schema::hasTable('films'), 'Az "films" tábla nem létezik.');

        $columns = DB::select('DESCRIBE films');
        $this->assertContains('id', array_column($columns, 'Field'));
        $this->assertContains('title', array_column($columns, 'Field'));
        $this->assertContains('production', array_column($columns, 'Field'));
        $this->assertContains('length', array_column($columns, 'Field'));
        $this->assertContains('presentation', array_column($columns, 'Field'));
        $this->assertContains('imdbLink', array_column($columns, 'Field'));
        $this->assertEquals('int(11)', $columns[0]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('varchar(60)', $columns[1]->Type); // Feltételezzük, hogy az 'osztalyNev' a második
        $this->assertEquals('int(11)', $columns[2]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('int(11)', $columns[3]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('date', $columns[4]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('varchar(255)', $columns[5]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        // Elsődleges kulcs ellenőrzése
        $primaryKeys = DB::select('SHOW KEYS FROM films WHERE Key_name = "PRIMARY"');
        $this->assertCount(1, $primaryKeys);
        $this->assertEquals('id', $primaryKeys[0]->Column_name);
    }

    public function test_videos_table_structure()
    {
        // Ellenőrizzük, hogy a tábla létezik
        $this->assertTrue(Schema::hasTable('videos'));
        // Ellenőrizzük a mezőket és azok típusait
        $this->assertTrue(Schema::hasColumn('videos', 'id'));
        $this->assertTrue(Schema::hasColumn('videos', 'filmId'));
        $this->assertTrue(Schema::hasColumn('videos', 'link'));
        $this->assertTrue(Schema::hasColumn('videos', 'embedLink'));
        $this->assertEquals('int', Schema::getColumnType('videos', 'id'));
        $this->assertEquals('int', Schema::getColumnType('videos', 'filmId'));

        $this->assertEquals('varchar', Schema::getColumnType('videos', 'link'));
        $this->assertEquals('varchar', Schema::getColumnType('videos', 'embedLink'));

        $this->assertTrue(Schema::hasColumn('videos', 'id'));
        $this->assertTrue(Schema::hasColumn('videos', 'filmId'));
        //Elsődleges kulcs
        $indexes = DB::select('SHOW INDEX FROM videos');
        $primaryIndex = collect($indexes)->firstWhere('Key_name', 'PRIMARY');
        $this->assertNotNull($primaryIndex);
    }
    public function test_favourites_table_structure()
    {
        // Ellenőrizzük, hogy a tábla létezik
        $this->assertTrue(Schema::hasTable('favourites'));
        // Ellenőrizzük a mezőket és azok típusait
        $this->assertTrue(Schema::hasColumn('favourites', 'id'));
        $this->assertTrue(Schema::hasColumn('favourites', 'userId'));
        $this->assertTrue(Schema::hasColumn('favourites', 'filmId'));
        $this->assertTrue(Schema::hasColumn('favourites', 'evaluation'));
        $this->assertEquals('int', Schema::getColumnType('favourites', 'id'));
        $this->assertEquals('int', Schema::getColumnType('favourites', 'userId'));
        $this->assertEquals('int', Schema::getColumnType('favourites', 'filmId'));
        $this->assertEquals('decimal', Schema::getColumnType('favourites', 'evaluation'));

        $this->assertTrue(Schema::hasColumn('favourites', 'id'));
        $this->assertTrue(Schema::hasColumn('favourites', 'userId'));
        $this->assertTrue(Schema::hasColumn('favourites', 'filmId'));
        //Elsődleges kulcs
        $indexes = DB::select('SHOW INDEX FROM favourites');
        $primaryIndex = collect($indexes)->firstWhere('Key_name', 'PRIMARY');
        $this->assertNotNull($primaryIndex);
    }

    public function test_roles_table_structure()
    {
        $this->assertTrue(Schema::hasTable('roles'), 'Az "roles" tábla nem létezik.');

        $columns = DB::select('DESCRIBE roles');
        $this->assertContains('id', array_column($columns, 'Field'));
        $this->assertContains('role', array_column($columns, 'Field'));
        $this->assertEquals('int(11)', $columns[0]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('varchar(255)', $columns[1]->Type); // Feltételezzük, hogy az 'osztalyNev' a második
        // Elsődleges kulcs ellenőrzése
        $primaryKeys = DB::select('SHOW KEYS FROM roles WHERE Key_name = "PRIMARY"');
        $this->assertCount(1, $primaryKeys);
        $this->assertEquals('id', $primaryKeys[0]->Column_name);
    }

    public function test_people_table_structure()
    {
        $this->assertTrue(Schema::hasTable('people'), 'Az "people" tábla nem létezik.');

        $columns = DB::select('DESCRIBE people');
        $this->assertContains('id', array_column($columns, 'Field'));
        $this->assertContains('name', array_column($columns, 'Field'));
        $this->assertContains('gender', array_column($columns, 'Field'));
        $this->assertContains('photo', array_column($columns, 'Field'));
        $this->assertContains('imdbLink', array_column($columns, 'Field'));
        $this->assertEquals('int(11)', $columns[0]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('varchar(60)', $columns[1]->Type); // Feltételezzük, hogy az 'osztalyNev' a második
        $this->assertEquals('tinyint(1)', $columns[2]->Type); // Feltételezzük, hogy az 'id' az első oszlop
        $this->assertEquals('varchar(90)', $columns[3]->Type); // Feltételezzük, hogy az 'osztalyNev' a második
        $this->assertEquals('varchar(255)', $columns[4]->Type); // Feltételezzük, hogy az 'osztalyNev' a második
        // Elsődleges kulcs ellenőrzése
        $primaryKeys = DB::select('SHOW KEYS FROM people WHERE Key_name = "PRIMARY"');
        $this->assertCount(1, $primaryKeys);
        $this->assertEquals('id', $primaryKeys[0]->Column_name);
    }

    public function test_tasks_table_structure()
    {
        // Ellenőrizzük, hogy a tábla létezik
        $this->assertTrue(Schema::hasTable('tasks'));
        // Ellenőrizzük a mezőket és azok típusait
        $this->assertTrue(Schema::hasColumn('tasks', 'id'));
        $this->assertTrue(Schema::hasColumn('tasks', 'filmId'));
        $this->assertTrue(Schema::hasColumn('tasks', 'personId'));
        $this->assertTrue(Schema::hasColumn('tasks', 'roleId'));
        $this->assertEquals('int', Schema::getColumnType('tasks', 'id'));
        $this->assertEquals('int', Schema::getColumnType('tasks', 'filmId'));
        $this->assertEquals('int', Schema::getColumnType('tasks', 'personId'));
        $this->assertEquals('int', Schema::getColumnType('tasks', 'roleId'));
        //dd(Schema::getColumnType('tasks', 'name'));
        $this->assertTrue(Schema::hasColumn('tasks', 'id'));
        $this->assertTrue(Schema::hasColumn('tasks', 'filmId'));
        $this->assertTrue(Schema::hasColumn('tasks', 'personId'));
        $this->assertTrue(Schema::hasColumn('tasks', 'roleId'));
        //Elsődleges kulcs
        $indexes = DB::select('SHOW INDEX FROM tasks');
        $primaryIndex = collect($indexes)->firstWhere('Key_name', 'PRIMARY');
        $this->assertNotNull($primaryIndex);
    }

    public function test_users_positions_relationships()
    {
        $databaseName = env('DB_DATABASE');
        $tableName = "users";
        $contstraint_name = "PRIMARY";

        $query = "
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE
            WHERE
                TABLE_NAME = ? and CONSTRAINT_SCHEMA = ? and CONSTRAINT_NAME <> ?";

        $rows = DB::select($query, [$tableName, $databaseName, $contstraint_name]);
        $this->assertEquals('positionId', $rows[1]->COLUMN_NAME);
        $this->assertEquals('positions', $rows[1]->REFERENCED_TABLE_NAME);
        $this->assertEquals('id', $rows[1]->REFERENCED_COLUMN_NAME);

        $dataPosition =
            [
                'name' => 'vendeg'
            ];
        $position = Position::factory()->create($dataPosition);

        $dataUser =
            [
                'positionId' => $position->id,
                'name' => 'test7',
                'email' => 'test7@example.com',
                'password' => 1234567
            ];
        $user = User::factory()->create($dataUser);

        $user = DB::table('users')
            ->where('id', $user->id)
            ->first();
        $this->assertEquals($position->id, $user->positionId);
    }

    public function test_favourites_users_relationships()
    {
        $databaseName = env('DB_DATABASE');
        $tableName = "favourites";
        $constraint_name = "PRIMARY";
    
        $query = "
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE
            WHERE
                TABLE_NAME = ? and CONSTRAINT_SCHEMA = ? and CONSTRAINT_NAME <> ?";
    
        $rows = DB::select($query, [$tableName, $databaseName, $constraint_name]);
    
        $this->assertEquals('userId', $rows[1]->COLUMN_NAME);
        $this->assertEquals('users', $rows[1]->REFERENCED_TABLE_NAME);
        $this->assertEquals('id', $rows[1]->REFERENCED_COLUMN_NAME);
        // Create a user
        $dataUser = ['name' => 'Fredd'];
        $user = User::factory()->create($dataUser);
        // Create a favourite record, referencing the userId
        $dataFavourites = [
            'userId' => $user->id,  // Referencing the existing user ID
            'filmId' => 11,
            'evaluation' => 2.4,
        ];
        $favourites = Favourite::factory()->create($dataFavourites);  // Creating a Favourites record
        // Verify the relationship
        $favourite = DB::table('favourites')  // Query the 'favourites' table
            ->where('id', $favourites->id)
            ->first();
        $this->assertEquals($user->id, $favourite->userId);  // Ensure the userId matches
    }
}
