<?php

namespace App\Console\Commands;

use App\Services\EuroleagueStatsService;
use Illuminate\Console\Command;

class SyncSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'euroleague:sync-schedule
                            {season=E2025 : The season code (e.g., E2024, E2025)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync future/upcoming games from Euroleague v2 API (games not yet played)';

    /**
     * Execute the console command.
     */
    public function handle(EuroleagueStatsService $service): int
    {
        $seasonCode = $this->argument('season');

        $this->info("Syncing future games for season {$seasonCode}...");
        $this->info("(Only games that haven't been played yet)");
        $this->newLine();

        $results = $service->syncScheduleFromV2Api($seasonCode, true);

        $this->newLine();
        $this->info("Future games sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $results['created']],
                ['Skipped (already in DB)', $results['skipped']],
                ['Failed', $results['failed']],
            ]
        );

        // Show upcoming games
        $this->newLine();
        $this->info("📅 Upcoming games in database:");
        $upcoming = $service->getUpcomingGames($seasonCode, 10);

        if (count($upcoming) > 0) {
            $upcomingTable = [];
            foreach ($upcoming as $game) {
                $upcomingTable[] = [
                    'Game' => $game['gameCode'],
                    'Round' => $game['round'],
                    'Date' => date('Y-m-d H:i', strtotime($game['date'])),
                    'Matchup' => ($game['local']['club']['abbreviatedName'] ?? 'Unknown') . ' vs ' . ($game['road']['club']['abbreviatedName'] ?? 'Unknown'),
                ];
            }

            $this->table(
                ['Game', 'Round', 'Date', 'Matchup'],
                $upcomingTable
            );

            $this->newLine();
            $this->info("✅ " . count($upcoming) . " upcoming games are now in the database!");
            $this->info("After these games are played, fetch their stats with:");
            $this->line("php artisan euroleague:fetch-stats --game=<GAME_CODE>");
        } else {
            $this->warn("No upcoming games found. All games for this season may have been played.");
        }

        return Command::SUCCESS;
    }
}

