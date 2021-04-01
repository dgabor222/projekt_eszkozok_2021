<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    private const FirstPlayerNumber = 1;
    private const SecondPlayerNumber = 6;

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function firstPlayer() {
        return $this->belongsTo(User::class, 'first_player');
    }

    public function secondPlayer() {
        return $this->belongsTo(User::class, 'second_player');
    }

    public function latestPlayer() {
        return $this->belongsTo(User::class, 'latest_player');
    }

    public function winnedBy() {
        return $this->belongsTo(User::class, 'winned_by');
    }

    public function steps() {
        return $this->hasMany(Step::class);
    }

    // Megmondja, hogy egy pozíció koordinátái a pályán belül vannak-e.
    private function isValidPosition($row, $col) {
        return
            ($row >= 1 && $row <= $this->map_height) &&
            ($col >= 1 && $col <= $this->map_width);
    }

    // Megmondja, hogy egy adott pozíció szabad-e még, tehát azt, hogy lépett-e oda valaki.
    private function isPositionAvailable($row, $col) {
        return $this->steps()->where(compact('row', 'col'))->count() < 1;
    }

    // Módosítja a legutóbb lépett játékost.
    private function updateLatestPlayer(User $player) {
        $this->latestPlayer()->associate($player);
        return $this->save();
    }

    // Készít egy mátrixot, aminek minden eleme nulla.
    static public function createZeroMatrix($rows, $cols) {
        return array_fill(0, $rows, array_fill(0, $cols, 0));
    }

    // Lekéri a játékhoz tartozó lépéseket, és oly módon képezi le a játékteret egy mátrixba, hogy az a mező, ahova...
    //    - még nem léptek, 0 értékű legyen;
    //    - ahová az első játékos lépett, az 1 legyen;
    //    - ahová pedig a második játékos lépett, az 6 legyen.
    public function getMapAsMatrix() {
        $matrix = Game::createZeroMatrix($this->map_height, $this->map_width);
        $this->steps->each(function ($step) use (&$matrix) {
            $player = $step->player;
            $col = $step->col-1;
            $row = $step->row-1;
            if ($player && $player->is($this->firstPlayer)) {
                $matrix[$row][$col] = self::FirstPlayerNumber;
            } else if ($player && $player->is($this->secondPlayer)) {
                $matrix[$row][$col] = self::SecondPlayerNumber;
            }
        });
        return $matrix;
    }

    // Lekéri azt a balról jobbra haladó átlót (pongyolán "főátlót"), amelyiknek az adott koordináta is a része.
    static public function getLeftToRightDiagonal($matrix, $row, $col) {
        return array_filter(
            array_map(function ($k) use ($matrix, $col, $row) {
                return $matrix[$k][$k + $col - $row] ?? null;
            }, array_keys($matrix)),
            function ($e) {
                return $e !== null;
            }
        );
    }

    // Lekéri azt a jobbról balra haladó átlót (pongyolán "mellékátlót"), amelyiknek az adott koordináta is a része.
    static public function getRightToLeftDiagonal($matrix, $row, $col) {
        return array_filter(
            array_map(function ($k) use ($matrix, $col, $row) {
                return $matrix[$k][$col - $k + $row] ?? null;
            }, array_keys($matrix)),
            function ($e) {
                return $e !== null;
            }
        );
    }

    // Megadja, hogy egy sort, oszlopot vagy átlót megnyert-e egy játékos.
    static public function checkPartiallyWin($part) {
        $length = count($part);
        for ($i = 0; $i < $length-4; $i++) {
            $slice = array_slice($part, $i, 5);
            $sum = array_sum($slice);
            if ($sum == 5 || $sum == 30) return true;
        }
        return false;
    }

    // Minden lépés után megnézzük, hogy a játékot megnyerték-e. Ezzel a módszerrel visszafelé bebiztosítjuk magunkat, hiszen folyamatosan vizsgáljuk a nyerést, vagyis nem kell mindig a teljes mátrixot vizsgálni.
    // Ez a metódus pontosan egy adott pozícióból (ahová a legutóbb léptek) mondja meg, hogy történt-e nyerés.
    static public function checkWin($matrix, $row, $col) {
        // Megnézzük az adott pozícióhoz tartozó sort, oszlopot, illetve a bal- és jobb oldali átlókat.
        return
            Game::checkPartiallyWin($matrix[$row-1]) ||
            Game::checkPartiallyWin(array_column($matrix, $col-1)) ||
            Game::checkPartiallyWin(Game::getLeftToRightDiagonal($matrix, $row-1, $col-1)) ||
            Game::checkPartiallyWin(Game::getRightToLeftDiagonal($matrix, $row-1, $col-1));
    }

    // Megadja, hogy egy sortban, oszlopban vagy átlóban alakulhat-e még ki nyerés.
    static public function checkPartiallyTie($part) {
        $length = count($part);
        for ($i = 0; $i < $length-4; $i++) {
            $slice = array_slice($part, $i, 5);
            $sum = array_sum($slice);
            if ($sum <= 5 || $sum % 6 == 0) return true;
        }
        return false;
    }

    // Minden lépés után megnézzük, hogy a játékot még érdemes-e folytatni, vagy már döntetlen helyzet alakult ki, és nem lehet a játékot olyan módon befejezni, hogy azt biztosan megnyerhesse az egyik játékos.
    static public function checkTie($matrix) {
        $width = count($matrix[0]);
        for ($i = 0; $i < $width; $i++) {
            if(Game::checkPartiallyTie($matrix[$i]) ||
            Game::checkPartiallyTie(array_column($matrix, $i))) return false;
        }
        for ($i = 0; $i < $width*2-1; $i++) {
            if(Game::checkPartiallyTie(Game::getLeftToRightDiagonal($matrix, 1, $i)) ||
            Game::checkPartiallyTie(Game::getRightToLeftDiagonal($matrix, $i, 1))) return false;
        }
        return true;
    }

    // Játékos csatlakoztatása.
    public function join(User $player) {
        if ($this->status !== 'WAITING') return 1;
        if ($this->isFull()) return 2;
        if (!$this->first_player) $this->firstPlayer()->associate($player);
        if (!$this->second_player) $this->secondPlayer()->associate($player);
        return $this->save();
    }

    // Megadja, hogy egy játékos benne van-e a játékban.
    public function isPlayerInThisGame(User $player) {
        return $player->is($this->firstPlayer) || $player->is($this->secondPlayer);
    }

    // Megadja, hogy mindkét játékos csatlakozva van-e.
    public function isFull() {
        return $this->firstPlayer !== null && $this->secondPlayer !== null;
    }

    // Játékos kiléptetése.
    public function leave(User $player) {
        if (!$this->isPlayerInThisGame($player)) return 1;
        return $this->abandon();
    }

    // Lépés megtétele egy adott játékossal egy adott pozícióra.
    public function step(User $player, $row, $col) {
        // Vizsgálni kell a kizáró okokat:
        if ($this->status !== 'STARTED') return 1;              // Csak elindított játékban lehet lépni.
        if (!$this->isPlayerInThisGame($player)) return 2;      // Csak olyan játékos léphet, aki részt vesz a játékban.
        if ($player->is($this->latestPlayer)) return 3;         // Ugyanaz a játékos nem léphet kétszer egymás után.
        if (!$this->isValidPosition($row, $col)) return 4;      // Nem lehet a játéktéren kívülre lépni.
        if (!$this->isPositionAvailable($row, $col)) return 5;  // Nem lehet olyan helyre lépni, ahová már valaki lépett korábban.

        // Lépés regisztrálása, majd a legutóbbi játékos frissítése
        $step = new Step;
        $step->game_id = $this->id;
        $step->player()->associate($player);
        $step->row = $row;
        $step->col = $col;
        if(!$step->save()) return 6;

        $this->updateLatestPlayer($player);

        // Ellenőrizni kell a nyerést és a döntetlen helyzetet is:
        $matrix = $this->getMapAsMatrix();
        if (Game::checkWin($matrix, $row, $col)) {
            $this->winnedBy()->associate($player);
            $this->end();
            return 7;
        }
        if (Game::checkTie($matrix)) {
            $this->end();
            return 8;
        }

        // Ezzel csak jelezzük, hogy a lépés sikeres volt.
        return 0;
    }

    // Játék elindítása.
    public function start() {
        if ($this->status !== 'WAITING') return 1;
        if (!$this->isFull()) return 2;

        $this->started_at = now();
        $this->status = 'STARTED';
        if (!$this->save()) return 3;

        return 0;
    }

    // Segédfv. a játék befejezéséhez/megszakításához
    private function endWithStatus($status) {
        if ($this->status !== 'STARTED') return 1;

        $this->ended_at = now();
        $this->status = $status;
        if (!$this->save()) return 3;

        return 0;
    }

    // Játék megszakítása (ez nem ugyanaz, mint a sima befejezés, erre van az 'ABANDONED' status).
    public function abandon() {
        return $this->endWithStatus('ABANDONED');
    }

    // Játék befejezése.
    public function end() {
        return $this->endWithStatus('ENDED');
    }
}
