<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGameStat extends Model
{
    protected $fillable = [
        'game_id',
        'player_id',
        'team_id',
        'dorsal',
        'position',
        'is_starter',
        'is_playing',
        'minutes',
        'points',
        'field_goals_made_2',
        'field_goals_attempted_2',
        'field_goals_made_3',
        'field_goals_attempted_3',
        'free_throws_made',
        'free_throws_attempted',
        'offensive_rebounds',
        'defensive_rebounds',
        'total_rebounds',
        'assists',
        'steals',
        'turnovers',
        'blocks_favor',
        'blocks_against',
        'fouls_committed',
        'fouls_received',
        'valuation',
        'plus_minus',
    ];

    protected $casts = [
        'is_starter' => 'boolean',
        'is_playing' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

