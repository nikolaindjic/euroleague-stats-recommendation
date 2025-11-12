<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Console\Command;

class InspectGame extends Command
{
    protected $signature = 'debug:inspect-game {gameCode}';
    protected $description = 'Inspect a specific game and show all players with their positions and minutes';

    public function handle(): int
    {
        $gameCode = $this->argument('gameCode');

        $game = Game::where('game_code', $gameCode)->first();

        if (!$game) {
            $this->error("Game {$gameCode} not found");
            return Command::FAILURE;
        }

        $this->info("Game Code: {$game->game_code}");
        $this->info("Season: {$game->season_code}");
        $this->newLine();

        $teams = $game->teamStats;

        foreach ($teams as $teamStat) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Team: {$teamStat->team->team_name}");
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            $players = PlayerGameStat::where('game_id', $game->id)
                ->where('team_id', $teamStat->team_id)
                ->orderBy('minutes', 'desc')
                ->get();

            $tableData = [];
            foreach ($players as $playerStat) {
                $player = Player::find($playerStat->player_id);
                $tableData[] = [
                    'Player' => $player->player_name,
                    'Position' => $playerStat->position ?? 'N/A',
                    'Minutes' => $playerStat->minutes ?? '0',
                    'Points' => $playerStat->points,
                    '18+ Min' => ($playerStat->minutes >= 18) ? '✓' : '✗'
                ];
            }

            $this->table(['Player', 'Position', 'Minutes', 'Points', '18+ Min'], $tableData);
            $this->newLine();

            // Count guards with 18+ minutes
            $guardsCount = PlayerGameStat::where('game_id', $game->id)
                ->where('team_id', $teamStat->team_id)
                ->where('minutes', '>=', 18)
                ->where(function($query) {
                    $query->where('position', 'LIKE', '%G%')
                          ->orWhere('position', 'LIKE', '%Guard%');
                })
                ->count();

            $this->line("Guards with 18+ minutes: {$guardsCount}");
            $this->newLine();
        }

        return Command::SUCCESS;
    }
}

