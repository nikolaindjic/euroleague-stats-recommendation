<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\EuroleagueStatsService;

echo "Fetching E2025 schedule...\n\n";

$service = app(EuroleagueStatsService::class);

try {
    $results = $service->syncScheduleFromV2Api('E2025', true);

    echo "Schedule sync completed!\n";
    echo "======================\n";
    echo "Created: " . $results['created'] . "\n";
    echo "Skipped: " . $results['skipped'] . "\n";
    echo "Failed:  " . $results['failed'] . "\n";
    echo "\n";

    // Show upcoming games
    echo "Fetching upcoming games...\n";
    $upcoming = $service->getUpcomingGames('E2025', 10);

    if (count($upcoming) > 0) {
        echo "\nNext " . count($upcoming) . " upcoming games:\n";
        echo "=====================================\n";

        foreach ($upcoming as $game) {
            $date = date('Y-m-d H:i', strtotime($game['date']));
            $home = $game['local']['club']['abbreviatedName'] ?? 'Unknown';
            $away = $game['road']['club']['abbreviatedName'] ?? 'Unknown';
            echo "Game {$game['gameCode']} | Round {$game['round']} | {$date}\n";
            echo "  {$home} vs {$away}\n\n";
        }
    } else {
        echo "\nNo upcoming games found.\n";
    }

    echo "✅ Done!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

