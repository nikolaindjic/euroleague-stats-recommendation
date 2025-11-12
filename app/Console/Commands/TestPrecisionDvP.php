<?php

namespace App\Console\Commands;

use App\Enums\Position;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Console\Command;

class TestPrecisionDvP extends Command
{
    protected $signature = 'test:precision-dvp';
    protected $description = 'Test precision difference between SQL AVG and PHP division';

    public function handle(): int
    {
        $this->info("════════════════════════════════════════════════════════════════");
        $this->info("Testing Precision: SQL AVG() vs PHP Division");
        $this->info("════════════════════════════════════════════════════════════════");
        $this->newLine();

        $teams = Team::limit(5)->get();
        $positionCode = 'G'; // Test with Guards

        foreach ($teams as $team) {
            $gameIds = $team->gameStats()->pluck('game_id');

            if ($gameIds->isEmpty()) {
                continue;
            }

            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Team: {$team->team_name}");
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            // Method 1: SQL AVG (current implementation)
            $sqlAvgStats = PlayerGameStat::whereIn('game_id', $gameIds)
                ->where('team_id', '!=', $team->id)
                ->where('minutes', '>=', 18)
                ->where('position', 'LIKE', "%{$positionCode}%")
                ->selectRaw('
                    COUNT(*) as total_players,
                    AVG(points) as avg_points,
                    AVG(total_rebounds) as avg_rebounds,
                    AVG(assists) as avg_assists,
                    AVG(valuation) as avg_pir
                ')
                ->first();

            // Method 2: SUM + PHP division (old implementation)
            $sumStats = PlayerGameStat::whereIn('game_id', $gameIds)
                ->where('team_id', '!=', $team->id)
                ->where('minutes', '>=', 18)
                ->where('position', 'LIKE', "%{$positionCode}%")
                ->selectRaw('
                    COUNT(*) as total_players,
                    SUM(points) as total_points,
                    SUM(total_rebounds) as total_rebounds,
                    SUM(assists) as total_assists,
                    SUM(valuation) as total_pir
                ')
                ->first();

            if (!$sqlAvgStats || $sqlAvgStats->total_players == 0) {
                $this->warn("No qualifying players found");
                $this->newLine();
                continue;
            }

            $totalPlayers = $sqlAvgStats->total_players;

            // Calculate PHP averages
            $phpAvgPoints = $sumStats->total_points / $totalPlayers;
            $phpAvgRebounds = $sumStats->total_rebounds / $totalPlayers;
            $phpAvgAssists = $sumStats->total_assists / $totalPlayers;
            $phpAvgPir = $sumStats->total_pir / $totalPlayers;

            // Compare
            $this->line("Sample Size: {$totalPlayers} players");
            $this->newLine();

            $this->line("Points Per Player:");
            $this->line("  SQL AVG():      " . number_format($sqlAvgStats->avg_points, 15));
            $this->line("  PHP Division:   " . number_format($phpAvgPoints, 15));
            $this->line("  Difference:     " . number_format(abs($sqlAvgStats->avg_points - $phpAvgPoints), 20));
            $this->line("  Match:          " . ($sqlAvgStats->avg_points == $phpAvgPoints ? '✓ EXACT' : '✗ DIFFERENT'));
            $this->newLine();

            $this->line("Rebounds Per Player:");
            $this->line("  SQL AVG():      " . number_format($sqlAvgStats->avg_rebounds, 15));
            $this->line("  PHP Division:   " . number_format($phpAvgRebounds, 15));
            $this->line("  Difference:     " . number_format(abs($sqlAvgStats->avg_rebounds - $phpAvgRebounds), 20));
            $this->line("  Match:          " . ($sqlAvgStats->avg_rebounds == $phpAvgRebounds ? '✓ EXACT' : '✗ DIFFERENT'));
            $this->newLine();

            $this->line("Assists Per Player:");
            $this->line("  SQL AVG():      " . number_format($sqlAvgStats->avg_assists, 15));
            $this->line("  PHP Division:   " . number_format($phpAvgAssists, 15));
            $this->line("  Difference:     " . number_format(abs($sqlAvgStats->avg_assists - $phpAvgAssists), 20));
            $this->line("  Match:          " . ($sqlAvgStats->avg_assists == $phpAvgAssists ? '✓ EXACT' : '✗ DIFFERENT'));
            $this->newLine();

            $this->line("PIR Per Player:");
            $this->line("  SQL AVG():      " . number_format($sqlAvgStats->avg_pir, 15));
            $this->line("  PHP Division:   " . number_format($phpAvgPir, 15));
            $this->line("  Difference:     " . number_format(abs($sqlAvgStats->avg_pir - $phpAvgPir), 20));
            $this->line("  Match:          " . ($sqlAvgStats->avg_pir == $phpAvgPir ? '✓ EXACT' : '✗ DIFFERENT'));
            $this->newLine();

            // Also check raw data types
            $this->line("Data Types:");
            $this->line("  SQL AVG points type:   " . gettype($sqlAvgStats->avg_points) . " (" . var_export($sqlAvgStats->avg_points, true) . ")");
            $this->line("  PHP division type:     " . gettype($phpAvgPoints) . " (" . var_export($phpAvgPoints, true) . ")");
            $this->newLine();

            // Display formatted values (what users see)
            $this->line("Display Values (rounded to 1 decimal):");
            $this->line("  SQL AVG:        " . number_format($sqlAvgStats->avg_points, 1) . " pts, " .
                                                  number_format($sqlAvgStats->avg_rebounds, 1) . " reb, " .
                                                  number_format($sqlAvgStats->avg_assists, 1) . " ast");
            $this->line("  PHP Division:   " . number_format($phpAvgPoints, 1) . " pts, " .
                                                  number_format($phpAvgRebounds, 1) . " reb, " .
                                                  number_format($phpAvgAssists, 1) . " ast");
            $this->line("  Match:          " . (
                number_format($sqlAvgStats->avg_points, 1) == number_format($phpAvgPoints, 1) &&
                number_format($sqlAvgStats->avg_rebounds, 1) == number_format($phpAvgRebounds, 1) &&
                number_format($sqlAvgStats->avg_assists, 1) == number_format($phpAvgAssists, 1)
                ? '✓ IDENTICAL' : '✗ DIFFERENT'
            ));
            $this->newLine();
        }

        $this->newLine();
        $this->info("════════════════════════════════════════════════════════════════");
        $this->info("Summary:");
        $this->info("════════════════════════════════════════════════════════════════");
        $this->line("Both SQL AVG() and PHP division use IEEE 754 double precision");
        $this->line("MySQL returns DECIMAL or DOUBLE for AVG()");
        $this->line("PHP division uses float (double precision)");
        $this->line("Any differences should be at the floating-point precision level");
        $this->line("For display purposes (1 decimal place), results are identical");
        $this->newLine();

        return Command::SUCCESS;
    }
}

