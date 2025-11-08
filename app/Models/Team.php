<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'team_code',
        'team_name',
    ];

    public function gameStats(): HasMany
    {
        return $this->hasMany(TeamGameStat::class);
    }
}

