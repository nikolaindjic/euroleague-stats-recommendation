<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Console\Command;

class FindBaskoniaGuards extends Command
{
    protected $signature = 'debug:baskonia-guards';
    protected $description = 'Find Baskonia games with no opposing guards with 18+ minutes';

    public function handle(): int
    {
        $baskonia = Team::where('team_name', 'LIKE', '%Baskonia%')->first();

        if (!$baskonia) {
            $this->error('Baskonia team not found');
            return Command::FAILURE;
        }

        $this->info("Checking Baskonia games for opposing guards with 18+ minutes...");
        $this->newLine();

        $gameIds = $baskonia->gameStats()->pluck('game_id');

        $results = [];

        foreach ($gameIds as $gameId) {
            $game = Game::find($gameId);

            $opponentGuards = PlayerGameStat::where('game_id', $gameId)
                ->where('team_id', '!=', $baskonia->id)
                ->where('minutes', '>=', 18)
                ->where(function($query) {
                    $query->where('position', 'LIKE', '%G%')
                          ->orWhere('position', 'LIKE', '%Guard%');
                })
                ->get();

            $count = $opponentGuards->count();

            $results[] = [
                'Game Code' => $game->game_code,
                'Guards 18+' => $count,
                'Status' => $count === 0 ? '❌ NO GUARDS' : '✓'
            ];
        }

        $this->table(['Game Code', 'Guards 18+', 'Status'], $results);

        $noGuards = collect($results)->where('Guards 18+', 0);

        if ($noGuards->count() > 0) {
            $this->newLine();
            $this->warn("Found {$noGuards->count()} game(s) with NO opposing guards with 18+ minutes:");
            foreach ($noGuards as $game) {
                $this->line("  - Game Code: {$game['Game Code']}");
            }
        } else {
            $this->newLine();
            $this->info("All games had opposing guards with 18+ minutes!");
        }

        return Command::SUCCESS;
    }
}

