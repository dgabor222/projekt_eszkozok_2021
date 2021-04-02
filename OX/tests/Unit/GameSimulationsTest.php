<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;

use App\Models\Game;
use App\Models\User;

// Ez a teszt konkrét játékokat szimulál le
class GameSimulationsTest extends TestCase
{
    use DatabaseMigrations;

    // Minden teszteset előtt tiszta in-memory adatbázist készítünk.
    public function setUp(): void {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed --class UserSeeder');
    }

    // Minden teszteset után reseteljük az in-memory adatbázisunkat.
    public function tearDown(): void {
        $this->artisan('migrate:reset');
    }

    // Konkrét játékok leszimulálása.
    public function test_simulation_win() {
        $this->assertNull(User::find(1)->currentGame());
        $this->assertEquals(User::find(1)->createGame(), true);
        $game = User::find(1)->currentGame();
        $this->assertNotNull($game);
        $this->assertEquals(User::find(1)->step(1,1), "InvalidGameStatus");
        $this->assertEquals(User::find(1)->step(1,1), "InvalidGameStatus");
        User::find(2)->joinGame($game);
        $this->assertEquals($game->start(), true);
        $this->assertEquals(User::find(1)->step(1,1), true);
        $this->assertEquals(User::find(1)->step(1,1), "DuplicatedStep");
        $this->assertEquals(User::find(2)->step(111,1), "InvalidPosition");
        $this->assertEquals(User::find(2)->step(1,1), "UnavailablePosition");

        $this->assertEquals(User::find(2)->step(12,1), true);
        $this->assertEquals(User::find(1)->step(2,2), true);
        $this->assertEquals(User::find(2)->step(12,2), true);
        $this->assertEquals(User::find(1)->step(3,3), true);
        $this->assertEquals(User::find(2)->step(12,3), true);
        $this->assertEquals(User::find(1)->step(4,4), true);
        $this->assertEquals(User::find(2)->step(12,4), true);
        $this->assertEquals(User::find(1)->step(5,5), "GameWinned");

        $this->assertEquals(Game::find(1)->winnedBy, User::find(1));
    }

    public function test_simulation_tie() {
        $this->assertEquals(User::find(1)->createGame(), true);
        $game = User::find(1)->currentGame();
        $this->assertNotNull($game);
        User::find(2)->joinGame($game);
        $this->assertEquals($game->start(), true);

        $firstPlayer = true;
        for ($i = 0; $i < 12; $i++) {
            if (((int)($i/2)) % 2 == 0) {
                for ($j = 0; $j < 12; $j++) {
                    User::find($firstPlayer === true ? 1 : 2)->step($i+1, $j+1);
                    $firstPlayer = !$firstPlayer;
                }
            } else {
                for ($j = 11; $j >= 0; $j--) {
                    User::find($firstPlayer === true ? 1 : 2)->step($i+1, $j+1);
                    $firstPlayer = !$firstPlayer;
                }
            }
        }

        $game->refresh();
        $this->assertEquals($game->status, 'ENDED');
        $this->assertNull($game->winnedBy);
    }
}

