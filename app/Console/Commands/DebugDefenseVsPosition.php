<?php

namespace App\Console\Commands;

use App\Enums\Position;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Console\Command;

class DebugDefenseVsPosition extends Command
{
    protected $signature = 'debug:dvp {teamId} {position=G}';
    protected $description = 'Debug Defense vs Position calculation for a specific team';

    public function handle(): int
    {
        $teamId = $this->argument('teamId');
        $positionCode = $this->argument('position');

        $team = Team::find($teamId);

        if (!$team) {
            $this->error("Team with ID {$teamId} not found");
            return Command::FAILURE;
        }

        $this->info("═══════════════════════════════════════════════════");
        $this->info("Defense vs Position Debug for: {$team->team_name}");
        $this->info("Position Filter: {$positionCode}");
        $this->info("═══════════════════════════════════════════════════");
        $this->newLine();

        // Get games where this team played
        $gameIds = $team->gameStats()->pluck('game_id')->toArray();
        $totalGames = count($gameIds);

        $this->line("📅 Total Games Played: {$totalGames}");
        $this->line("🎮 Game IDs: " . implode(', ', $gameIds));
        $this->newLine();

        // Get ALL opponent player stats at this position with 18+ minutes
        $opponentPlayers = PlayerGameStat::whereIn('game_id', $gameIds)
            ->where('team_id', '!=', $team->id)
            ->where('minutes', '>=', 18)
            ->where('position', 'LIKE', "%{$positionCode}%")
            ->with(['player', 'game'])
            ->orderBy('game_id')
            ->get();

        $this->line("👥 Total Qualifying Opponent Players: {$opponentPlayers->count()}");
        $this->newLine();

        // Show breakdown by game
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("BREAKDOWN BY GAME:");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $byGame = $opponentPlayers->groupBy('game_id');
        foreach ($byGame as $gameId => $players) {
            $game = $players->first()->game;
            $this->line("Game {$gameId} ({$game->game_code}): {$players->count()} qualifying {$positionCode} players");
        }
        $this->newLine();

        // Calculate aggregate stats
        $opponentStats = PlayerGameStat::whereIn('game_id', $gameIds)
            ->where('team_id', '!=', $team->id)
            ->where('minutes', '>=', 18)
            ->where('position', 'LIKE', "%{$positionCode}%")
            ->selectRaw('
                COUNT(*) as total_players,
                AVG(points) as avg_points,
                AVG(total_rebounds) as avg_rebounds,
                AVG(assists) as avg_assists,
                AVG(valuation) as avg_pir,
                AVG(steals) as avg_steals,
                AVG(blocks_favor) as avg_blocks,
                SUM(points) as total_points,
                SUM(total_rebounds) as total_rebounds_sum,
                SUM(assists) as total_assists_sum
            ')
            ->first();

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("DEFENSE VS POSITION RESULTS:");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        $this->info("Sample Size:");
        $this->line("  • Total Qualifying Players: {$opponentStats->total_players}");
        $this->line("  • Games Played: {$totalGames}");
        $this->line("  • Players Per Game (avg): " . number_format($opponentStats->total_players / max($totalGames, 1), 2));
        $this->newLine();

        $this->info("Aggregate Totals:");
        $this->line("  • Total Points: " . number_format($opponentStats->total_points, 0));
        $this->line("  • Total Rebounds: " . number_format($opponentStats->total_rebounds_sum, 0));
        $this->line("  • Total Assists: " . number_format($opponentStats->total_assists_sum, 0));
        $this->newLine();

        $this->info("Per-Player Averages (Database AVG):");
        $this->line("  • Points Per Player: " . number_format($opponentStats->avg_points, 2));
        $this->line("  • Rebounds Per Player: " . number_format($opponentStats->avg_rebounds, 2));
        $this->line("  • Assists Per Player: " . number_format($opponentStats->avg_assists, 2));
        $this->line("  • PIR Per Player: " . number_format($opponentStats->avg_pir, 2));
        $this->line("  • Steals Per Player: " . number_format($opponentStats->avg_steals, 2));
        $this->line("  • Blocks Per Player: " . number_format($opponentStats->avg_blocks, 2));
        $this->newLine();

        $this->info("Manual Verification:");
        $manualAvgPoints = $opponentStats->total_points / max($opponentStats->total_players, 1);
        $this->line("  • Manual Avg Points (Total / Players): " . number_format($manualAvgPoints, 2));
        $this->line("  • Matches DB AVG: " . (abs($manualAvgPoints - $opponentStats->avg_points) < 0.01 ? '✓ YES' : '✗ NO'));
        $this->newLine();

        // Show top 5 opponent players as examples
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("TOP 5 OPPONENT PLAYERS (by points):");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $topPlayers = $opponentPlayers->sortByDesc('points')->take(5);
        $tableData = [];
        foreach ($topPlayers as $playerStat) {
            $tableData[] = [
                'Player' => $playerStat->player->player_name,
                'Position' => $playerStat->position,
                'Minutes' => $playerStat->minutes,
                'Points' => $playerStat->points,
                'Rebounds' => $playerStat->total_rebounds,
                'Assists' => $playerStat->assists,
                'PIR' => $playerStat->valuation,
            ];
        }

        $this->table(
            ['Player', 'Position', 'Minutes', 'Points', 'Rebounds', 'Assists', 'PIR'],
            $tableData
        );

        return Command::SUCCESS;
    }
}

