<?php

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchPlayerPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'euroleague:fetch-positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update player positions from Euroleague API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching player positions from Euroleague API...');

        $players = Player::whereNull('position')->orWhere('position', '')->get();

        if ($players->isEmpty()) {
            $this->info('All players already have positions assigned!');
            return Command::SUCCESS;
        }

        $this->info("Found {$players->count()} players without positions");

        $progressBar = $this->output->createProgressBar($players->count());
        $progressBar->start();

        $updated = 0;
        $failed = 0;

        foreach ($players as $player) {
            $position = $this->fetchPlayerPosition($player->player_id);

            if ($position) {
                $player->update(['position' => $position]);

                // Also update player_game_stats
                \App\Models\PlayerGameStat::where('player_id', $player->id)
                    ->update(['position' => $position]);

                $updated++;
            } else {
                $failed++;
            }

            $progressBar->advance();

            // Small delay to avoid overwhelming the API
            usleep(200000); // 0.2 seconds
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✓ Updated: {$updated} players");
        $this->warn("✗ Failed: {$failed} players");

        return Command::SUCCESS;
    }

    /**
     * Fetch player position from API
     */
    private function fetchPlayerPosition(string $playerId): ?string
    {
        try {
            $url = "https://api-live.euroleague.net/v1/players?playerCode={$playerId}&seasonCode=E2025";

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                Log::warning("Failed to fetch position for player ID {$playerId}: HTTP " . $response->status());
                return null;
            }

            $data = $response->json();

            // Try different possible field names
            return $data['position'] ?? $data['Position'] ?? $data['pos'] ?? null;

        } catch (\Exception $e) {
            return null;
        }
    }
}
