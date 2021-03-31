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
}
