<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\PlayerGameStat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillPlayerGameStatsPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:fill-positions
                            {--force : Update all positions, even non-null ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill player_game_stats positions from players table where players have non-null positions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fill player game stats positions...');

        // Get all players with non-null positions
        $playersWithPositions = Player::whereNotNull('position')
            ->where('position', '!=', '')
            ->get();

        if ($playersWithPositions->isEmpty()) {
            $this->warn('No players found with positions set.');
            return Command::SUCCESS;
        }

        $this->info("Found {$playersWithPositions->count()} players with positions.");

        $totalUpdated = 0;
        $bar = $this->output->createProgressBar($playersWithPositions->count());
        $bar->start();

        foreach ($playersWithPositions as $player) {
            // Build query for updating player game stats
            $query = PlayerGameStat::where('player_id', $player->id);

            // If not forcing, only update null positions
            if (!$this->option('force')) {
                $query->where(function ($q) {
                    $q->whereNull('position')
                      ->orWhere('position', '');
                });
            }

            // Update the positions
            $updated = $query->update(['position' => $player->position]);

            $totalUpdated += $updated;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully updated {$totalUpdated} player game stat records.");

        // Show summary statistics
        $this->newLine();
        $this->table(
            ['Position', 'Count'],
            [
                ['Guards (G)', PlayerGameStat::where('position', 'G')->count()],
                ['Forwards (F)', PlayerGameStat::where('position', 'F')->count()],
                ['Centers (C)', PlayerGameStat::where('position', 'C')->count()],
                ['NULL/Empty', PlayerGameStat::whereNull('position')->orWhere('position', '')->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
