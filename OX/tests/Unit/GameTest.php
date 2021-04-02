<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;

use App\Models\Game;
use App\Models\User;

class GameTest extends TestCase
{
    use DatabaseMigrations;

    // Minden teszteset előtt tiszta in-memory adatbázist készítünk.
    public function setUp(): void {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    // Minden teszteset után reseteljük az in-memory adatbázisunkat.
    public function tearDown(): void {
        $this->artisan('migrate:reset');
    }

    // Nullmátrix generálás letesztelése
    public function test_createZeroMatrix() {
        // Üres mátrix esetek
        $this->assertEquals(Game::createZeroMatrix(0, 0), []);
        $this->assertEquals(Game::createZeroMatrix(0, 1), []);
        $this->assertEquals(Game::createZeroMatrix(0, 2), []);

        // Különböző dimenziók esetei
        $this->assertEquals(Game::createZeroMatrix(1, 1), [
            [0]
        ]);

        $this->assertEquals(
            Game::createZeroMatrix(2, 2), [
            [0, 0],
            [0, 0],
        ]);

        $this->assertEquals(
            Game::createZeroMatrix(4, 4), [
            [0, 0, 0, 0],
            [0, 0, 0, 0],
            [0, 0, 0, 0],
            [0, 0, 0, 0],
        ]);

        $this->assertEquals(
            Game::createZeroMatrix(12, 12), [
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ]);
    }

    // Átlók lekérése egy adott pozícióból
    public function test_getLeftToRightDiagonal() {
        $matrix = [
            [0,   1,   2,   3],
            [4,   5,   6,   7],
            [8,   9,  10,  11],
            [12, 13,  14,  15]
        ];

        $this->assertEquals(Game::getLeftToRightDiagonal($matrix, 1, 2), [1,6,11]);
        $this->assertEquals(Game::getLeftToRightDiagonal($matrix, 0, 0), [0,5,10,15]);

        // Ezek a játékban nem fognak előfordulni, mivel már előtte kiszűrjük őket, csak a metódus teljes körű tesztelését szolgálják.
        $this->assertEquals(Game::getLeftToRightDiagonal($matrix, 4, 4), [0,5,10,15]);
        $this->assertEquals(Game::getLeftToRightDiagonal([[]], 0, 0), []);
    }

    public function test_getRightToLeftDiagonal() {
        $matrix = [
            [0,   1,   2,   3],
            [4,   5,   6,   7],
            [8,   9,  10,  11],
            [12, 13,  14,  15]
        ];

        $this->assertEquals(Game::getRightToLeftDiagonal($matrix, 1, 2), [3,6,9,12]);
        $this->assertEquals(Game::getRightToLeftDiagonal($matrix, 0, 0), [0]);

        // Ezek a játékban nem fognak előfordulni, mivel már előtte kiszűrjük őket, csak a metódus teljes körű tesztelését szolgálják.
        $this->assertEquals(Game::getRightToLeftDiagonal($matrix, 4, 4), []);
        $this->assertEquals(Game::getRightToLeftDiagonal([[]], 0, 0), []);
    }

    // Teszteljük, hogy a játékosnak valamilyen irányban sikerült-e kirakni a szimbólumokat.
    //
    // A számok jelentése:
    //   0 - még nem lépett oda senki
    //   1 - az 1. játékos lépett oda
    //   6 - a 2. játékos lépett oda
    public function test_checkPartiallyWin() {
        $this->assertFalse(Game::checkPartiallyWin([]));
        $this->assertTrue( Game::checkPartiallyWin([0, 0, 6, 1, 1, 1, 1, 1, 0, 0, 0, 0]));
        $this->assertFalse(Game::checkPartiallyWin([0, 0, 6, 1, 1, 1, 1, 0, 1, 0, 0, 0]));
        $this->assertFalse(Game::checkPartiallyWin([0, 0, 6, 6, 1, 1, 1, 0, 1, 0, 0, 0]));
        $this->assertFalse(Game::checkPartiallyWin([0, 0, 6, 6]));
        $this->assertTrue( Game::checkPartiallyWin([6, 6, 6, 6, 6]));
        $this->assertTrue( Game::checkPartiallyWin([1, 6, 6, 6, 6, 6]));
        $this->assertTrue( Game::checkPartiallyWin([1, 1, 1, 1, 1, 6, 6, 6, 6, 6]));
    }

    // Itt a logika hasonló, mint a partial win esetében, csak megadjuk a teljes játékteret, és egy adott pozíciót (az éles játékba ez lesz, ahová a legutóbbi játékos legutóbb lépett), és megvizsgáljuk, hogy abból a lépésből meg van-e nyerve a játék.
    //
    // A számok jelentése is ugyanaz, mint a partially win-nél:
    //   0 - még nem lépett oda senki
    //   1 - az 1. játékos lépett oda
    //   6 - a 2. játékos lépett oda
    public function test_win() {
        $matrix = [
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];
        $this->assertTrue(Game::checkWin($matrix, 4, 4));

        $matrix = [
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];
        $this->assertFalse(Game::checkWin($matrix, 4, 4));

        $matrix = [
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];
        $this->assertFalse(Game::checkWin($matrix, 4, 4));

        $matrix = [
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];
        $this->assertTrue(Game::checkWin($matrix, 2, 11));
    }

    // Döntetlenek tesztelése
    public function test_checkPartiallyTie() {
        $this->assertFalse(Game::checkPartiallyTie([]));
        $this->assertTrue( Game::checkPartiallyTie([0, 1, 1, 1, 1]));
        $this->assertFalse(Game::checkPartiallyTie([6, 1, 1, 1, 1]));
        $this->assertTrue(Game::checkPartiallyTie( [0, 0, 6, 6, 1, 1, 1, 0, 1, 0, 0, 0]));
        $this->assertFalse(Game::checkPartiallyTie([0, 0, 6, 6]));
        $this->assertTrue( Game::checkPartiallyTie([6, 6, 6, 6, 6]));
        $this->assertTrue( Game::checkPartiallyTie([1, 6, 6, 6, 6, 6]));
        $this->assertTrue( Game::checkPartiallyTie([1, 1, 1, 1, 1, 6, 6, 6, 6, 6]));
    }

    public function test_tie() {
        $matrix = [
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
        ];
        $this->assertFalse(Game::checkTie($matrix));

        $matrix = [
            [1, 1, 0, 1, 1],
            [6, 6, 1, 1, 6],
            [1, 1, 6, 6, 1],
            [6, 6, 1, 1, 6],
            [1, 1, 6, 6, 1],
        ];
        $this->assertFalse(Game::checkTie($matrix));

        $matrix = [
            [1, 1, 6, 6, 1],
            [6, 6, 1, 1, 6],
            [1, 1, 6, 6, 1],
            [6, 6, 1, 1, 6],
            [1, 1, 6, 6, 1],
        ];
        $this->assertTrue(Game::checkTie($matrix));

        $matrix = [
            [1, 1, 1, 1, 1],
            [6, 6, 1, 1, 6],
            [1, 1, 6, 6, 1],
            [6, 6, 1, 1, 6],
            [1, 1, 6, 6, 1],
        ];
        $this->assertFalse(Game::checkTie($matrix));
    }
}
