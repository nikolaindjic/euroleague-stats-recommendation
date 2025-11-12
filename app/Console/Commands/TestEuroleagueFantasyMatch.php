<?php

namespace App\Console\Commands;

use App\Enums\Position;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Console\Command;

class TestEuroleagueFantasyMatch extends Command
{
    protected $signature = 'test:ef-match {teamName?}';
    protected $description = 'Test different thresholds to match EuroLeague Fantasy values';

    // EuroLeague Fantasy reference data for Guards (ALL GAMES - 4th column - CORRECTED)
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
        $teamName = $this->argument('teamName');

        if ($teamName) {
            $teams = Team::where('team_name', 'LIKE', "%{$teamName}%")->get();
        } else {
            $teams = Team::all();
        }

        $this->info("════════════════════════════════════════════════════════════════");
        $this->info("Testing Different Calculation Methods vs EuroLeague Fantasy");
        $this->info("Position: Guards");
        $this->info("════════════════════════════════════════════════════════════════");
        $this->newLine();

        $minuteThresholds = [15, 18, 20, 22, 25];
        $positionCode = 'G';

        $results = [];

        foreach ($teams as $team) {
            $gameIds = $team->gameStats()->pluck('game_id')->toArray();

            if (empty($gameIds)) {
                continue;
            }

            $efValue = $this->euroleagueFantasyData[$team->team_name] ?? null;

            if (!$efValue) {
                continue; // Skip teams not in reference data
            }

            $teamResults = [
                'team' => $team->team_name,
                'ef_value' => $efValue,
                'games' => count($gameIds),
            ];

            // Test different minute thresholds
            foreach ($minuteThresholds as $threshold) {
                $stats = PlayerGameStat::whereIn('game_id', $gameIds)
                    ->where('team_id', '!=', $team->id)
                    ->where('minutes', '>=', $threshold)
                    ->where('position', 'LIKE', "%{$positionCode}%")
                    ->selectRaw('
                        COUNT(*) as total_players,
                        AVG(points) as avg_points
                    ')
                    ->first();

                $teamResults["min_{$threshold}"] = [
                    'value' => $stats->avg_points ?? 0,
                    'players' => $stats->total_players ?? 0,
                    'diff' => abs(($stats->avg_points ?? 0) - $efValue),
                ];
            }

            $results[] = $teamResults;
        }

        // Display results
        $this->table(
            ['Team', 'EF Value', 'Games', '15+ min', '18+ min', '20+ min', '22+ min', '25+ min'],
            collect($results)->map(function($r) {
                return [
                    'Team' => substr($r['team'], 0, 25),
                    'EF Value' => number_format($r['ef_value'], 1),
                    'Games' => $r['games'],
                    '15+ min' => number_format($r['min_15']['value'], 1) . ' (' . $r['min_15']['players'] . 'p)',
                    '18+ min' => number_format($r['min_18']['value'], 1) . ' (' . $r['min_18']['players'] . 'p)',
                    '20+ min' => number_format($r['min_20']['value'], 1) . ' (' . $r['min_20']['players'] . 'p)',
                    '22+ min' => number_format($r['min_22']['value'], 1) . ' (' . $r['min_22']['players'] . 'p)',
                    '25+ min' => number_format($r['min_25']['value'], 1) . ' (' . $r['min_25']['players'] . 'p)',
                ];
            })->toArray()
        );

        // Find best match
        $this->newLine();
        $this->info("════════════════════════════════════════════════════════════════");
        $this->info("Finding Best Match (Lowest Average Difference)");
        $this->info("════════════════════════════════════════════════════════════════");
        $this->newLine();

        foreach ($minuteThresholds as $threshold) {
            $avgDiff = collect($results)->avg("min_{$threshold}.diff");
            $this->line("Minutes >= {$threshold}: Avg difference = " . number_format($avgDiff, 2));
        }

        $this->newLine();
        $this->info("Looking at specific teams:");
        $this->newLine();

        // Show EA7 Milan and Real Madrid in detail
        $milanResult = collect($results)->firstWhere('team', 'EA7 EMPORIO ARMANI MILAN');
        $realResult = collect($results)->firstWhere('team', 'REAL MADRID');

        if ($milanResult) {
            $this->warn("EA7 EMPORIO ARMANI MILAN:");
            $this->line("  EuroLeague Fantasy: " . $milanResult['ef_value']);
            foreach ($minuteThresholds as $threshold) {
                $value = $milanResult["min_{$threshold}"];
                $match = abs($value['value'] - $milanResult['ef_value']) < 0.5 ? '✓' : '';
                $this->line("  {$threshold}+ min: " . number_format($value['value'], 2) . " ({$value['players']} players) {$match}");
            }
        }

        $this->newLine();

        if ($realResult) {
            $this->warn("REAL MADRID:");
            $this->line("  EuroLeague Fantasy: " . $realResult['ef_value']);
            foreach ($minuteThresholds as $threshold) {
                $value = $realResult["min_{$threshold}"];
                $match = abs($value['value'] - $realResult['ef_value']) < 0.5 ? '✓' : '';
                $this->line("  {$threshold}+ min: " . number_format($value['value'], 2) . " ({$value['players']} players) {$match}");
            }
        }

        return Command::SUCCESS;
    }
}

