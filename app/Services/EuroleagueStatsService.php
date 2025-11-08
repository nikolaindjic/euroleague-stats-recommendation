<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerGameStat;
use App\Models\Team;
use App\Models\TeamGameStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EuroleagueStatsService
{
    private const API_BASE_URL = 'https://live.euroleague.net/api/Boxscore';
    private const PLAYER_API_URL = 'https://api-live.euroleague.net/v1/players';

    /**
     * Fetch and store game data for a specific game
     */
    public function fetchAndStoreGame(int $gameCode, string $seasonCode): bool
    {
        try {
            $url = self::API_BASE_URL . "?gamecode={$gameCode}&seasoncode={$seasonCode}";

            Log::info("Fetching game data", ['game_code' => $gameCode, 'season_code' => $seasonCode]);

            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::warning("Failed to fetch game", ['game_code' => $gameCode, 'status' => $response->status()]);
                return false;
            }

            $data = $response->json();

            if (empty($data) || !isset($data['Stats'])) {
                Log::warning("Invalid data structure", ['game_code' => $gameCode]);
                return false;
            }

            $this->processAndStoreGameData($gameCode, $seasonCode, $data);

            Log::info("Successfully stored game data", ['game_code' => $gameCode]);
            return true;

        } catch (\Exception $e) {
            Log::error("Error fetching game", [
                'game_code' => $gameCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Fetch multiple games for a season
     */
    public function fetchGamesForSeason(string $seasonCode, int $startGame = 1, int $endGame = 100): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        for ($gameCode = $startGame; $gameCode <= $endGame; $gameCode++) {
            // Check if game already exists
            if (Game::where('game_code', $gameCode)->where('season_code', $seasonCode)->exists()) {
                Log::info("Game already exists, skipping", ['game_code' => $gameCode]);
                $results['skipped']++;
                continue;
            }

            $success = $this->fetchAndStoreGame($gameCode, $seasonCode);

            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            // Small delay to avoid overwhelming the API
            usleep(500000); // 0.5 seconds
        }

        return $results;
    }

    /**
     * Process and store game data in the database
     */
    private function processAndStoreGameData(int $gameCode, string $seasonCode, array $data): void
    {
        DB::transaction(function () use ($gameCode, $seasonCode, $data) {
            // Create or update game
            $round = (int) ceil($gameCode / 10);
            $game = Game::updateOrCreate(
                ['game_code' => $gameCode, 'season_code' => $seasonCode],
                [
                    'round' => $round,
                    'is_live' => $data['Live'] ?? false,
                    'referees' => $data['Referees'] ?? null,
                    'attendance' => $data['Attendance'] ?? null,
                ]
            );

            // Process both teams
            foreach ($data['Stats'] as $teamData) {
                $this->processTeamData($game, $teamData, $data['ByQuarter'] ?? []);
            }
        });
    }

    /**
     * Process team data and player stats
     */
    private function processTeamData(Game $game, array $teamData, array $byQuarter): void
    {
        $teamName = $teamData['Team'];
        $teamCode = $teamData['PlayersStats'][0]['Team'] ?? $this->extractTeamCode($teamName);

        // Create or get team
        $team = Team::firstOrCreate(
            ['team_code' => $teamCode],
            ['team_name' => $teamName]
        );

        // Get quarter scores for this team
        $quarterScores = $this->getQuarterScores($teamName, $byQuarter);

        // Create team game stats
        $totalStats = $teamData['totr'] ?? [];

        TeamGameStat::updateOrCreate(
            ['game_id' => $game->id, 'team_id' => $team->id],
            [
                'coach' => $teamData['Coach'] ?? null,
                'quarter1' => $quarterScores['q1'] ?? 0,
                'quarter2' => $quarterScores['q2'] ?? 0,
                'quarter3' => $quarterScores['q3'] ?? 0,
                'quarter4' => $quarterScores['q4'] ?? 0,
                'total_points' => $totalStats['Points'] ?? 0,
                'field_goals_made_2' => $totalStats['FieldGoalsMade2'] ?? 0,
                'field_goals_attempted_2' => $totalStats['FieldGoalsAttempted2'] ?? 0,
                'field_goals_made_3' => $totalStats['FieldGoalsMade3'] ?? 0,
                'field_goals_attempted_3' => $totalStats['FieldGoalsAttempted3'] ?? 0,
                'free_throws_made' => $totalStats['FreeThrowsMade'] ?? 0,
                'free_throws_attempted' => $totalStats['FreeThrowsAttempted'] ?? 0,
                'offensive_rebounds' => $totalStats['OffensiveRebounds'] ?? 0,
                'defensive_rebounds' => $totalStats['DefensiveRebounds'] ?? 0,
                'total_rebounds' => $totalStats['TotalRebounds'] ?? 0,
                'assists' => $totalStats['Assistances'] ?? 0,
                'steals' => $totalStats['Steals'] ?? 0,
                'turnovers' => $totalStats['Turnovers'] ?? 0,
                'blocks_favor' => $totalStats['BlocksFavour'] ?? 0,
                'blocks_against' => $totalStats['BlocksAgainst'] ?? 0,
                'fouls_committed' => $totalStats['FoulsCommited'] ?? 0,
                'fouls_received' => $totalStats['FoulsReceived'] ?? 0,
                'valuation' => $totalStats['Valuation'] ?? 0,
            ]
        );

        // Process player stats
        foreach ($teamData['PlayersStats'] as $playerData) {
            // Skip team rebounds entry
            if (empty(trim($playerData['Player_ID'] ?? ''))) {
                continue;
            }

            $this->processPlayerData($game, $team, $playerData);
        }
    }

    /**
     * Process individual player data
     */
    private function processPlayerData(Game $game, Team $team, array $playerData): void
    {
        $playerId = trim($playerData['Player_ID']);
        $playerName = $playerData['Player'];

        // Fetch position from player API if not already set
        $position = $this->fetchPlayerPosition($playerId);

        // Create or get player
        $player = Player::firstOrCreate(
            ['player_id' => $playerId],
            [
                'player_name' => $playerName,
                'position' => $position,
            ]
        );

        // Update player name and position if changed
        if ($player->player_name !== $playerName || ($position && $player->position !== $position)) {
            $player->update([
                'player_name' => $playerName,
                'position' => $position ?? $player->position,
            ]);
        }

        // Create player game stats
        PlayerGameStat::updateOrCreate(
            ['game_id' => $game->id, 'player_id' => $player->id],
            [
                'team_id' => $team->id,
                'dorsal' => $playerData['Dorsal'] ?? null,
                'position' => $position ?? $playerData['Position'] ?? null,
                'is_starter' => (bool)($playerData['IsStarter'] ?? false),
                'is_playing' => (bool)($playerData['IsPlaying'] ?? false),
                'minutes' => $playerData['Minutes'] ?? null,
                'points' => $playerData['Points'] ?? 0,
                'field_goals_made_2' => $playerData['FieldGoalsMade2'] ?? 0,
                'field_goals_attempted_2' => $playerData['FieldGoalsAttempted2'] ?? 0,
                'field_goals_made_3' => $playerData['FieldGoalsMade3'] ?? 0,
                'field_goals_attempted_3' => $playerData['FieldGoalsAttempted3'] ?? 0,
                'free_throws_made' => $playerData['FreeThrowsMade'] ?? 0,
                'free_throws_attempted' => $playerData['FreeThrowsAttempted'] ?? 0,
                'offensive_rebounds' => $playerData['OffensiveRebounds'] ?? 0,
                'defensive_rebounds' => $playerData['DefensiveRebounds'] ?? 0,
                'total_rebounds' => $playerData['TotalRebounds'] ?? 0,
                'assists' => $playerData['Assistances'] ?? 0,
                'steals' => $playerData['Steals'] ?? 0,
                'turnovers' => $playerData['Turnovers'] ?? 0,
                'blocks_favor' => $playerData['BlocksFavour'] ?? 0,
                'blocks_against' => $playerData['BlocksAgainst'] ?? 0,
                'fouls_committed' => $playerData['FoulsCommited'] ?? 0,
                'fouls_received' => $playerData['FoulsReceived'] ?? 0,
                'valuation' => $playerData['Valuation'] ?? 0,
                'plus_minus' => $playerData['Plusminus'] ?? 0,
            ]
        );
    }

    /**
     * Extract quarter scores for a team
     */
    private function getQuarterScores(string $teamName, array $byQuarter): array
    {
        $scores = ['q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0];

        foreach ($byQuarter as $quarter) {
            if ($quarter['Team'] === $teamName) {
                $scores['q1'] = $quarter['Quarter1'] ?? 0;
                $scores['q2'] = $quarter['Quarter2'] ?? 0;
                $scores['q3'] = $quarter['Quarter3'] ?? 0;
                $scores['q4'] = $quarter['Quarter4'] ?? 0;
                break;
            }
        }

        return $scores;
    }

    /**
     * Extract team code from team name
     */
    private function extractTeamCode(string $teamName): string
    {
        // Extract the last word(s) from team name as code
        $parts = explode(' ', $teamName);
        return end($parts);
    }

    /**
     * Fetch player position from Euroleague Player API
     */
    private function fetchPlayerPosition(string $playerId): ?string
    {
        try {
            $url = self::PLAYER_API_URL . "/{$playerId}";

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                Log::debug("Failed to fetch player position", ['player_id' => $playerId, 'status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            // The API should return position in the response
            // Check various possible field names
            $position = $data['position'] ?? $data['Position'] ?? $data['pos'] ?? null;

            if ($position) {
                Log::debug("Fetched position for player", ['player_id' => $playerId, 'position' => $position]);
            }

            return $position;

        } catch (\Exception $e) {
            Log::debug("Error fetching player position", [
                'player_id' => $playerId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

