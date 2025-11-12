<?php

namespace App\Console\Commands;

use App\Services\EuroleagueStatsService;
use Illuminate\Console\Command;

class SyncPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'euroleague:sync-players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '⚠️ WARNING: This command will NOT work - v1 API uses incompatible player codes';

    /**
     * Execute the console command.
     */
    public function handle(EuroleagueStatsService $service): int
    {
        $this->warn("⚠️  WARNING: This command will likely fail for all players!");
        $this->warn("The v1 API uses different player codes than the Boxscore API.");
        $this->warn("Player codes in database (e.g., P004840) are not compatible with v1 API.");
        $this->newLine();

        if (!$this->confirm('Do you want to continue anyway?', false)) {
            $this->info('Command cancelled.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info("Attempting to sync players (this will likely fail)...");
        $this->newLine();

        $results = $service->syncExistingPlayers();

        $this->newLine();
        $this->info("Player sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $results['updated']],
                ['Failed', $results['failed']],
                ['Skipped', $results['skipped']],
            ]
        );

        if ($results['failed'] > 0 && $results['updated'] === 0) {
            $this->newLine();
            $this->error("As expected, all players failed due to incompatible player codes.");
            $this->info("Use 'php artisan euroleague:fetch-stats' instead to get player data.");
        }

        return Command::SUCCESS;
    }
}

