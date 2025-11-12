<?php

namespace App\Console\Commands;

use App\Enums\Position;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InvestigateDvPDifference extends Command
{
    protected $signature = 'investigate:dvp {teamName}';
    protected $description = 'Deep dive into why there are differences with EuroLeague Fantasy';

    // Reference data
    private $euroleagueFantasyData = [
        'BASKONIA VITORIA-GASTEIZ' => 15.2,
        'VALENCIA BASKET' => 14.1,
        'MACCABI RAPYD TEL AVIV' => 13.3,
        'FC BARCELONA' => 13.2,
        'EA7 EMPORIO ARMANI MILAN' => 12.5,
        'AS MONACO' => 12.3,
        'HAPOEL IBI TEL AVIV' => 12.3,
        'PARIS BASKETBALL' => 12.2,
        'REAL MADRID' => 12.0,
        'VIRTUS BOLOGNA' => 12.0,
        'FC BAYERN MUNICH' => 11.7,
        'OLYMPIACOS PIRAEUS' => 11.6,
        'PANATHINAIKOS AKTOR ATHENS' => 11.5,
        'ZALGIRIS KAUNAS' => 11.4,
        'DUBAI BASKETBALL' => 11.4,
        'ANADOLU EFES ISTANBUL' => 11.2,
        'CRVENA ZVEZDA MERIDIANBET BELGRADE' => 11.2,
        'FENERBAHCE BEKO ISTANBUL' => 11.1,
        'LDLC ASVEL VILLEURBANNE' => 11.0,
        'PARTIZAN MOZZART BET BELGRADE' => 10.6,
    ];

    public function handle(): int
    {
        $teamName = strtoupper($this->argument('teamName'));

        $team = Team::where('team_name', 'LIKE', "%{$teamName}%")->first();

        if (!$team) {
            $this->error("Team not found");
            return Command::FAILURE;
        }

        $efValue = $this->euroleagueFantasyData[$team->team_name] ?? null;

        if (!$efValue) {
            $this->error("No EuroLeague Fantasy reference data for this team");
            return Command::FAILURE;
        }

        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("Deep Investigation: {$team->team_name}");
        $this->info("EuroLeague Fantasy Value: {$efValue}");
        $this->info("═══════════════════════════════════════════════════════════");
        $this->newLine();

        $gameIds = $team->gameStats()->pluck('game_id')->toArray();

        $this->line("Total Games: " . count($gameIds));
        $this->newLine();

        // Get all opponent guards with different criteria
        $this->warn("Testing Different Scenarios:");
        $this->newLine();

        // Scenario 1: Current implementation (18+ min, LIKE '%G%')
        $current = PlayerGameStat::whereIn('game_id', $gameIds)
            ->where('team_id', '!=', $team->id)
            ->where('minutes', '>=', 18)
            ->where('position', 'LIKE', '%G%')
            ->selectRaw('COUNT(*) as cnt, AVG(points) as avg, SUM(points) as total')
            ->first();

        $this->line("1. Current (18+ min, LIKE '%G%'):");
        $this->line("   Players: {$current->cnt}");
        $this->line("   Avg: " . number_format($current->avg, 4));
        $this->line("   Total: {$current->total}");
        $this->line("   Diff from EF: " . number_format(abs($current->avg - $efValue), 4));
        $this->newLine();

        // Scenario 2: Exact position match
        $exact = PlayerGameStat::whereIn('game_id', $gameIds)
            ->where('team_id', '!=', $team->id)
            ->where('minutes', '>=', 18)
            ->where('position', '=', 'G')
            ->selectRaw('COUNT(*) as cnt, AVG(points) as avg, SUM(points) as total')
            ->first();

        $this->line("2. Exact match (position = 'G'):");
        $this->line("   Players: {$exact->cnt}");
        $this->line("   Avg: " . number_format($exact->avg, 4));
        $this->line("   Total: {$exact->total}");
        $this->line("   Diff from EF: " . number_format(abs($exact->avg - $efValue), 4));
        $this->newLine();

        // Scenario 3: Different minute thresholds
        foreach ([15, 16, 17, 18, 19, 20] as $threshold) {
            $test = PlayerGameStat::whereIn('game_id', $gameIds)
                ->where('team_id', '!=', $team->id)
                ->where('minutes', '>=', $threshold)
                ->where('position', 'LIKE', '%G%')
                ->selectRaw('COUNT(*) as cnt, AVG(points) as avg')
                ->first();

            $diff = abs($test->avg - $efValue);
            $match = $diff < 0.05 ? '✓✓✓' : ($diff < 0.5 ? '✓' : '');

            $this->line("{$threshold}+ min: " . number_format($test->avg, 4) . " ({$test->cnt}p) - Diff: " . number_format($diff, 4) . " {$match}");
        }
        $this->newLine();

        // Show all qualifying players
        $this->warn("All Qualifying Players (18+ min, LIKE '%G%'):");
        $this->newLine();

        $players = PlayerGameStat::whereIn('game_id', $gameIds)
            ->where('team_id', '!=', $team->id)
            ->where('minutes', '>=', 18)
            ->where('position', 'LIKE', '%G%')
            ->with(['player', 'game'])
            ->orderBy('points', 'desc')
            ->get();

        $tableData = [];
        foreach ($players as $ps) {
            $tableData[] = [
                'Player' => substr($ps->player->player_name, 0, 25),
                'Pos' => $ps->position,
                'Min' => $ps->minutes,
                'Pts' => $ps->points,
                'Game' => $ps->game->game_code,
            ];
        }

        $this->table(['Player', 'Pos', 'Min', 'Pts', 'Game'], $tableData);

        $this->newLine();
        $this->warn("Analysis:");
        $this->line("Total players: {$players->count()}");
        $this->line("Total points: {$players->sum('points')}");
        $this->line("Average: " . number_format($players->avg('points'), 6));
        $this->line("Manual calc: " . number_format($players->sum('points') / $players->count(), 6));

        $this->newLine();
        $this->line("Looking for the magic formula...");

        // Try to find what gives exact match
        $target = $efValue;
        $totalPoints = $players->sum('points');
        $neededPlayerCount = $totalPoints / $target;

        $this->line("To get {$target}, we need: {$totalPoints} / " . number_format($neededPlayerCount, 2) . " players");
        $this->line("We have: {$players->count()} players");
        $this->line("Difference: " . number_format($players->count() - $neededPlayerCount, 2) . " players");

        return Command::SUCCESS;
    }
}

