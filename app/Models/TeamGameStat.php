<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamGameStat extends Model
{
    protected $fillable = [
        'game_id',
        'team_id',
        'coach',
        'quarter1',
        'quarter2',
        'quarter3',
        'quarter4',
        'total_points',
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
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
