<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\TeamGameStat;
use App\Models\PlayerGameStat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldSeasonGames extends Command
{
    protected $signature = 'euroleague:cleanup-old-games {season=E2024}';
    protected $description = 'Remove all games from a specific season';

    public function handle(): int
    {
        $season = $this->argument('season');

        $this->warn("This will delete ALL games from season {$season} and their associated stats!");

        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->info('Cleanup cancelled.');
            return Command::SUCCESS;
        }

        $this->info("Deleting games from season {$season}...");

        DB::transaction(function () use ($season) {
            // Get all game IDs for this season
            $gameIds = Game::where('season_code', $season)->pluck('id');

            if ($gameIds->isEmpty()) {
                $this->info("No games found for season {$season}");
                return;
            }

            // Delete player stats
            $playerStatsDeleted = PlayerGameStat::whereIn('game_id', $gameIds)->delete();
            $this->info("Deleted {$playerStatsDeleted} player game stats");

            // Delete team stats
            $teamStatsDeleted = TeamGameStat::whereIn('game_id', $gameIds)->delete();
            $this->info("Deleted {$teamStatsDeleted} team game stats");

            // Delete games
            $gamesDeleted = Game::where('season_code', $season)->delete();
            $this->info("Deleted {$gamesDeleted} games");
        });

        $this->newLine();
        $this->info("✅ Cleanup completed for season {$season}!");

        return Command::SUCCESS;
    }
}

