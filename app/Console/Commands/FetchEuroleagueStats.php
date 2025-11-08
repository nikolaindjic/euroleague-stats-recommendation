<?php

namespace App\Console\Commands;

use App\Services\EuroleagueStatsService;
use Illuminate\Console\Command;

class FetchEuroleagueStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'euroleague:fetch-stats
                            {season=E2025 : The season code (e.g., E2025)}
                            {--start=1 : Starting game code}
                            {--end=100 : Ending game code}
                            {--game= : Fetch a specific game code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store Euroleague game statistics from the API';

    /**
     * Execute the console command.
     */
    public function handle(EuroleagueStatsService $service): int
    {
        $seasonCode = $this->argument('season');
        $specificGame = $this->option('game');

        if ($specificGame) {
            // Fetch a specific game
            $this->info("Fetching game {$specificGame} for season {$seasonCode}...");

            $success = $service->fetchAndStoreGame((int)$specificGame, $seasonCode);

            if ($success) {
                $this->info("✓ Successfully fetched and stored game {$specificGame}");
                return Command::SUCCESS;
            } else {
                $this->error("✗ Failed to fetch game {$specificGame}");
                return Command::FAILURE;
            }
        }

        // Fetch multiple games
        $start = (int)$this->option('start');
        $end = (int)$this->option('end');

        $this->info("Fetching games {$start} to {$end} for season {$seasonCode}...");

        $progressBar = $this->output->createProgressBar($end - $start + 1);
        $progressBar->start();

        $results = ['success' => 0, 'failed' => 0, 'skipped' => 0];

        for ($gameCode = $start; $gameCode <= $end; $gameCode++) {
            // Check if game already exists
            if (\App\Models\Game::where('game_code', $gameCode)
                ->where('season_code', $seasonCode)
                ->exists()) {
                $results['skipped']++;
                $progressBar->advance();
                continue;
            }

            $success = $service->fetchAndStoreGame($gameCode, $seasonCode);

            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $progressBar->advance();

            // Small delay to avoid overwhelming the API
            usleep(500000); // 0.5 seconds
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info("Fetch completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $results['success']],
                ['Failed', $results['failed']],
                ['Skipped', $results['skipped']],
            ]
        );

        return Command::SUCCESS;
    }
}

