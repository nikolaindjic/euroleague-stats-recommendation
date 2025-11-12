<?php

/**
 * Examples of how to use the Euroleague API integration in your code
 */

// Example 1: Fetch and update a specific player's details
use App\Services\EuroleagueStatsService;

$service = app(EuroleagueStatsService::class);

// Get player details from API
$playerData = $service->fetchPlayerDetails('P004840');
if ($playerData) {
    // Do something with the data
    echo "Player: " . $playerData['name'] ?? 'Unknown';
}

// Update player in database
$success = $service->updatePlayerFromApi('P004840');
if ($success) {
    echo "Player updated successfully!";
}

// Example 2: Sync all existing players
$results = $service->syncExistingPlayers();
echo "Updated: {$results['updated']}, Failed: {$results['failed']}, Skipped: {$results['skipped']}";

// Example 3: Fetch game details
$gameData = $service->fetchGameDetails(50, 'E2025');
if ($gameData) {
    // Process game data
    print_r($gameData);
}

// Example 4: Fetch and store complete game statistics
$success = $service->fetchAndStoreGame(50, 'E2025');
if ($success) {
    echo "Game 50 fetched and stored successfully!";
}

// Example 5: Force reload a game (overwrites existing stats)
$success = $service->fetchAndStoreGame(50, 'E2025', true);

// Example 6: Fetch multiple games
$results = $service->fetchGamesForSeason('E2025', 1, 97);
echo "Success: {$results['success']}, Failed: {$results['failed']}, Skipped: {$results['skipped']}";

// Example 7: Get player position (if available)
use App\Models\Player;

$player = Player::where('player_id', 'P004840')->first();
if ($player) {
    echo "Position: " . $player->position; // G, F, or C
}

// Example 8: Use in a controller to refresh a player
namespace App\Http\Controllers;

use App\Services\EuroleagueStatsService;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function refreshPlayer(Request $request, EuroleagueStatsService $service)
    {
        $playerCode = $request->input('player_code');

        $success = $service->updatePlayerFromApi($playerCode);

        if ($success) {
            return response()->json(['message' => 'Player refreshed successfully']);
        }

        return response()->json(['message' => 'Failed to refresh player'], 500);
    }
}

// Example 9: Fetch player position during game processing
// (This is already integrated in the EuroleagueStatsService)
// When processing game data, it automatically tries to fetch positions from the API

// Example 10: Check if a player has position data
$playersWithoutPosition = Player::whereNull('position')->count();
echo "Players without position: {$playersWithoutPosition}";

// You can then sync them:
// php artisan euroleague:sync-players

