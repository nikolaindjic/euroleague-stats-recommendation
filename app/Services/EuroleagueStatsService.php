<?php

namespace App\Services;

use App\Enums\Position;
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
    private const SCHEDULE_API_URL = 'https://api-live.euroleague.net/v2/competitions';
    private const COMPETITION_CODE = 'E'; // Euroleague
    private const TEAMS_API_URL = 'https://api-live.euroleague.net/v1/teams';

    /**
     * Fetch and store game data for a specific game
     */
    public function fetchAndStoreGame(int $gameCode, string $seasonCode, bool $force = false): bool
    {
        try {
            $url = self::API_BASE_URL . "?gamecode={$gameCode}&seasoncode={$seasonCode}";

            Log::info("Fetching game data", ['game_code' => $gameCode, 'season_code' => $seasonCode, 'force' => $force]);

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

            $this->processAndStoreGameData($gameCode, $seasonCode, $data, $force);

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
    private function processAndStoreGameData(int $gameCode, string $seasonCode, array $data, bool $force = false): void
    {
        DB::transaction(function () use ($gameCode, $seasonCode, $data, $force) {
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

            // If force mode, delete all existing team and player stats for this game
            if ($force) {
                Log::info("Force mode: Deleting existing stats for game", ['game_id' => $game->id, 'game_code' => $gameCode]);
                TeamGameStat::where('game_id', $game->id)->delete();
                PlayerGameStat::where('game_id', $game->id)->delete();
            }

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

        // If API didn't return position, try to normalize from playerData
        if (!$position && isset($playerData['Position'])) {
            $position = $this->normalizePosition($playerData['Position']);
        }

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
                'position' => $position ?? $player->position,
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
                // Normalize the position to our standard format
                return $this->normalizePosition($position);
            }

            return null;

        } catch (\Exception $e) {
            Log::debug("Error fetching player position", [
                'player_id' => $playerId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Normalize position string to standard format (G, F, or C)
     */
    private function normalizePosition(?string $position): ?string
    {
        if (!$position) {
            return null;
        }

        $position = strtoupper(trim($position));

        // Check for Guard variations
        if (preg_match('/\b(G|GUARD|PG|SG|POINT|SHOOTING)\b/i', $position)) {
            return Position::GUARD;
        }

        // Check for Forward variations
        if (preg_match('/\b(F|FORWARD|SF|PF|SMALL|POWER)\b/i', $position)) {
            return Position::FORWARD;
        }

        // Check for Center variations
        if (preg_match('/\b(C|CENTER|CENTRE)\b/i', $position)) {
            return Position::CENTER;
        }

        // If it's already one of our standard positions, return it
        if (in_array($position, Position::all())) {
            return $position;
        }

        // Default to null if we can't determine the position
        Log::debug("Unable to normalize position", ['position' => $position]);
        return null;
    }

    /**
     * Fetch player details from the Euroleague API by player code
     * Endpoint: GET /v1/players/{playerCode}
     *
     * ⚠️ WARNING: This endpoint uses DIFFERENT player codes than the Boxscore API
     * The player codes in our database (from Boxscore API) like P004840, P011927
     * are NOT compatible with the v1 API player codes.
     *
     * This method is provided for reference but will NOT work with existing data.
     */
    public function fetchPlayerDetails(string $playerCode): ?array
    {
        try {
            $url = self::PLAYER_API_URL . "?PlayerCode=" . $playerCode;

            Log::debug("Fetching player details", ['player_code' => $playerCode]);

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                Log::debug("Failed to fetch player details", ['player_code' => $playerCode, 'status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            if (empty($data)) {
                Log::debug("Empty player data", ['player_code' => $playerCode]);
                return null;
            }

            Log::debug("Successfully fetched player details", ['player_code' => $playerCode]);

            return $data;

        } catch (\Exception $e) {
            Log::debug("Error fetching player details", ['player_code' => $playerCode, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Update player details from API by player code
     */
    public function updatePlayerFromApi(string $playerCode): bool
    {
        $playerData = $this->fetchPlayerDetails($playerCode);

        if (!$playerData) {
            return false;
        }

        try {
            $playerId = $playerData['personId'] ?? $playerData['playerId'] ?? $playerData['id'] ?? $playerCode;
            $playerName = $playerData['name'] ?? $playerData['playerName'] ?? null;
            $position = $playerData['position'] ?? $playerData['pos'] ?? null;

            if (!$playerName) {
                Log::warning("Player name missing", ['player_code' => $playerCode, 'data' => $playerData]);
                return false;
            }

            $normalizedPosition = $this->normalizePosition($position);

            Player::updateOrCreate(
                ['player_id' => $playerId],
                [
                    'player_name' => $playerName,
                    'position' => $normalizedPosition,
                ]
            );

            Log::info("Updated player from API", ['player_code' => $playerCode, 'player_id' => $playerId]);
            return true;

        } catch (\Exception $e) {
            Log::error("Error updating player from API", [
                'player_code' => $playerCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Sync all existing players in the database by fetching their details from the API
     *
     * ⚠️ WARNING: This method will NOT work with current data!
     * The player_id values in the database come from Boxscore API (e.g., P004840)
     * but the v1 API requires different player codes. There is no mapping between them.
     *
     * This method will fail for all players with "404 Not Found" or similar errors.
     */
    public function syncExistingPlayers(): array
    {
        $results = ['updated' => 0, 'failed' => 0, 'skipped' => 0];

        $players = Player::all();

        Log::info("Syncing existing players from API", ['total_players' => $players->count()]);

        foreach ($players as $player) {
            if (empty($player->player_id)) {
                $results['skipped']++;
                continue;
            }

            $success = $this->updatePlayerFromApi($player->player_id);

            if ($success) {
                $results['updated']++;
            } else {
                $results['failed']++;
            }

            // Small delay to avoid overwhelming the API
            usleep(200000); // 0.2 seconds
        }

        Log::info("Player sync completed", $results);
        return $results;
    }

    /**
     * Fetch game details from the Euroleague API
     * Endpoint: GET /v1/games?gameCode={code}&seasonCode={season}
     */
    public function fetchGameDetails(int $gameCode, string $seasonCode = 'E2025'): ?array
    {
        try {
            $url = "https://api-live.euroleague.net/v1/games?gamecode={$gameCode}&seasoncode={$seasonCode}";

            Log::debug("Fetching game details", ['game_code' => $gameCode, 'season_code' => $seasonCode]);

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                Log::debug("Failed to fetch game details", [
                    'game_code' => $gameCode,
                    'status' => $response->status()
                ]);
                return null;
            }

            // Response is XML, convert to array
            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                Log::debug("Failed to parse XML", ['game_code' => $gameCode]);
                return null;
            }

            // Convert XML to array
            $data = json_decode(json_encode($xml), true);

            Log::debug("Successfully fetched game details", ['game_code' => $gameCode]);

            return $data;

        } catch (\Exception $e) {
            Log::debug("Error fetching game details", [
                'game_code' => $gameCode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch complete schedule from Euroleague v2 API
     * Endpoint: GET /v2/competitions/{competition}/seasons/{season}/games
     *
     * ✅ This endpoint works and returns complete schedule in JSON format
     */
    public function fetchCompleteSchedule(string $seasonCode = 'E2025'): array
    {
        try {
            $url = self::SCHEDULE_API_URL . "/" . self::COMPETITION_CODE . "/seasons/{$seasonCode}/games";

            Log::info("Fetching complete schedule", ['season_code' => $seasonCode, 'url' => $url]);

            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::error("Failed to fetch schedule", ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();

            if (!isset($data['data']) || !is_array($data['data'])) {
                Log::error("Invalid schedule data structure", ['data' => $data]);
                return [];
            }

            $games = $data['data'];
            Log::info("Successfully fetched schedule", ['count' => count($games), 'total' => $data['total'] ?? count($games)]);

            return $games;

        } catch (\Exception $e) {
            Log::error("Error fetching schedule", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Sync schedule from v2 API to database
     *
     * ✅ This method works with the v2 API and syncs all games
     *
     * @param string $seasonCode Season code (e.g., E2025)
     * @param bool $onlyFutureGames If true, only sync games that haven't been played yet
     */
    public function syncScheduleFromV2Api(string $seasonCode = 'E2025', bool $onlyFutureGames = true): array
    {
        $games = $this->fetchCompleteSchedule($seasonCode);
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($games as $gameData) {
            try {
                $gameCode = $gameData['gameCode'] ?? null;
                $round = $gameData['round'] ?? null;
                $homeTeam = $gameData['local'] ?? null;
                $awayTeam = $gameData['road'] ?? null;
                $played = $gameData['played'] ?? 0;
                $date = $gameData['date'] ?? null;
                $audience = $gameData['audience'] ?? null;

                if (!$gameCode) {
                    Log::warning("Skipping game with missing game code", ['data' => $gameData]);
                    $results['skipped']++;
                    continue;
                }

                // Skip played games if we only want future games
                if ($onlyFutureGames && $played) {
                    $results['skipped']++;
                    continue;
                }

                // Check if game already exists in database
                $existingGame = Game::where('game_code', $gameCode)
                    ->where('season_code', $seasonCode)
                    ->first();

                // If game exists and has stats, skip it
                if ($existingGame && TeamGameStat::where('game_id', $existingGame->id)->exists()) {
                    $results['skipped']++;
                    continue;
                }

                // If game exists but is a future game, skip it unless date changed
                if ($existingGame && !$played) {
                    $results['skipped']++;
                    continue;
                }

                $homeTeamCode = null;
                $awayTeamCode = null;
                $homeScore = null;
                $awayScore = null;

                // Create or update teams if provided
                if ($homeTeam && isset($homeTeam['club'])) {
                    $homeClub = $homeTeam['club'];
                    $homeTeamCode = $homeClub['code'];
                    $homeScore = $homeTeam['score'] ?? null;

                    Team::firstOrCreate(
                        ['team_code' => $homeClub['code']],
                        ['team_name' => $homeClub['name'] ?? $homeClub['abbreviatedName'] ?? $homeClub['code']]
                    );
                }

                if ($awayTeam && isset($awayTeam['club'])) {
                    $awayClub = $awayTeam['club'];
                    $awayTeamCode = $awayClub['code'];
                    $awayScore = $awayTeam['score'] ?? null;

                    Team::firstOrCreate(
                        ['team_code' => $awayClub['code']],
                        ['team_name' => $awayClub['name'] ?? $awayClub['abbreviatedName'] ?? $awayClub['code']]
                    );
                }

                // Create game (only future games at this point)
                $game = Game::create([
                    'game_code' => $gameCode,
                    'season_code' => $seasonCode,
                    'round' => $round,
                    'game_date' => $date ? date('Y-m-d H:i:s', strtotime($date)) : null,
                    'home_team_code' => $homeTeamCode,
                    'away_team_code' => $awayTeamCode,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'is_played' => false, // Only syncing future games
                    'is_live' => false,
                    'attendance' => $audience,
                ]);

                $results['created']++;
                Log::info("Created future game from schedule", [
                    'game_code' => $gameCode,
                    'round' => $round,
                    'date' => $date,
                    'home' => $homeTeamCode,
                    'away' => $awayTeamCode
                ]);

            } catch (\Exception $e) {
                // Check if it's a duplicate key error (game already exists)
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                    $results['skipped']++;
                    Log::debug("Game already exists", ['game_code' => $gameCode ?? 'unknown']);
                } else {
                    Log::error("Error syncing game", [
                        'game_code' => $gameCode ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    $results['failed']++;
                }
            }
        }

        Log::info("Schedule sync completed", $results);
        return $results;
    }

    /**
     * Get upcoming games (not yet played)
     */
    public function getUpcomingGames(string $seasonCode = 'E2025', int $limit = 10): array
    {
        $games = $this->fetchCompleteSchedule($seasonCode);

        $upcoming = array_filter($games, function($game) {
            return ($game['played'] ?? 1) == 0; // Not played yet
        });

        // Sort by date
        usort($upcoming, function($a, $b) {
            return strtotime($a['date'] ?? 0) - strtotime($b['date'] ?? 0);
        });

        return array_slice($upcoming, 0, $limit);
    }

    /**
     * Get next game codes that need stats fetched
     */
    public function getNextGamesToFetch(string $seasonCode = 'E2025', int $limit = 10): array
    {
        $schedule = $this->fetchCompleteSchedule($seasonCode);

        // Filter for played games that we don't have stats for yet
        $needStats = [];

        foreach ($schedule as $gameData) {
            $gameCode = $gameData['gameCode'] ?? null;
            $played = $gameData['played'] ?? 0;

            if (!$gameCode || $played == 0) {
                continue; // Skip games without code or not played yet
            }

            // Check if we have this game with stats in database
            $game = Game::where('game_code', $gameCode)
                ->where('season_code', $seasonCode)
                ->first();

            $hasStats = $game && TeamGameStat::where('game_id', $game->id)->exists();

            if (!$hasStats) {
                $needStats[] = [
                    'game_code' => $gameCode,
                    'round' => $gameData['round'] ?? null,
                    'date' => $gameData['date'] ?? null,
                    'home' => $gameData['local']['club']['name'] ?? 'Unknown',
                    'away' => $gameData['road']['club']['name'] ?? 'Unknown',
                ];
            }

            if (count($needStats) >= $limit) {
                break;
            }
        }

        return $needStats;
    }
}
