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
        'game_date',
        'home_team_code',
        'away_team_code',
        'home_score',
        'away_score',
        'is_played',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'is_played' => 'boolean',
        'game_date' => 'datetime',
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
