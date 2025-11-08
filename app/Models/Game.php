<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'game_code',
        'round',
        'season_code',
        'is_live',
        'referees',
        'attendance',
    ];

    protected $casts = [
        'is_live' => 'boolean',
    ];

    public function teamStats(): HasMany
    {
        return $this->hasMany(TeamGameStat::class);
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }
}
