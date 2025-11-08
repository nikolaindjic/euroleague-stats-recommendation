<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'player_id',
        'player_name',
        'position',
    ];

    public function gameStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }
}

