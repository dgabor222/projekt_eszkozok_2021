<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Step
 *
 * @property-read \App\Models\User $player
 * @method static \Database\Factories\StepFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Step newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Step newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Step query()
 * @mixin \Eloquent
 */
class Step extends Model
{
    use HasFactory;

    public function player() {
        return $this->belongsTo(User::class, 'player_id');
    }
}
