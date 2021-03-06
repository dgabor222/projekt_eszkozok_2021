<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $createdGames
 * @property-read int|null $created_games_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $gamesWhereFirstPlayer
 * @property-read int|null $games_where_first_player_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $gamesWhereSecondPlayer
 * @property-read int|null $games_where_second_player_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $winnedGames
 * @property-read int|null $winned_games_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Alapvető metódusok
    public function gamesWhereFirstPlayer() {
        return $this->hasMany(Game::class, 'first_player');
    }

    public function gamesWhereSecondPlayer() {
        return $this->hasMany(Game::class, 'second_player');
    }

    public function games() {
        return $this->gamesWhereFirstPlayer->merge($this->gamesWhereSecondPlayer);
    }

    public function createdGames() {
        return $this->hasMany(Game::class, 'created_by');
    }

    public function winnedGames() {
        return $this->hasMany(Game::class, 'winned_by');
    }

    // Megadja a játékoshoz tartozó aktuális játékot (vagyis ami várakozó vagy elindított állapotban van, tehát "élő", aktuális játék).
    public function currentGame() {
        return $this->games()->whereIn('status', ['WAITING','STARTED'])->first();
    }

    // Az előző, currentGame metódusra épül, megnézi, hogy a felhasználóhoz tartozik-e aktuális játék, amiben benne van.
    public function isInGame() {
        return $this->currentGame() !== null;
    }

    // Játék létrehozása, amelynek a creator-ja ez a felhasználó lesz.
    public function createGame() {
        if ($this->isInGame()) return "PlayerAlreadyInGame";

        $game = new Game;
        $game->createdBy()->associate($this);
        $game->firstPlayer()->associate($this);
        if (! $game->save()) return "SaveFailed";

        $this->refresh();
        return true;
    }

    // Csatlakozás egy már meglévő játékhoz, amennyiben az lehetséges.
    public function joinGame(Game $game) {
        if ($this->isInGame()) return "PlayerAlreadyInGame";

        $joinResult = $game->join($this);
        if ($joinResult !== true) return $joinResult;

        $this->refresh();
        return true;
    }

    // Aktuális játék elhagyása, amennyiben van olyan.
    public function leaveGame() {
        $game = $this->currentGame();
        if (! $game) return "PlayerNotInGame";

        $leaveResult = $game->leave($this);
        if ($leaveResult !== true) return $leaveResult;

        $this->refresh();
        return true;
    }

    // Lépés megtétele az aktuális játékban, amennyiben van olyan.
    public function step($row, $col) {
        $game = $this->currentGame();
        if (! $game) return "PlayerNotInGame";

        return $game->step($this, $row, $col);
    }
}
