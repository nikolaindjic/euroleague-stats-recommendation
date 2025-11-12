# Euroleague API Integration

This document describes how to use the Euroleague API integration to fetch player data and game information.

## API Endpoints

The integration uses the official Euroleague API v1:
- **Base URL**: `https://api-live.euroleague.net/v1`
- **Player Details Endpoint**: `/v1/players?PlayerCode={code}`
- **Game Details Endpoint**: `/v1/games?gameCode={code}&seasonCode={season}`
- **Game Stats (Legacy)**: `https://live.euroleague.net/api/Boxscore?gamecode={code}&seasoncode={season}`
- **Swagger Documentation**: https://api-live.euroleague.net/swagger/index.html

## Important Notes

The Euroleague API v1 endpoints are designed for **individual lookups**, not bulk data retrieval:
- `/v1/players` requires a specific `PlayerCode` parameter
- `/v1/games` requires specific `gameCode` and `seasonCode` parameters
- There is no endpoint to list all players or all games

For bulk data collection, we use the legacy Boxscore API which provides complete game statistics.

## Available Commands

### 1. Sync Player Details

Updates existing players in the database by fetching their details from the Euroleague API.

```bash
php artisan euroleague:sync-players
```

**What it does:**
- Iterates through all players already in the database
- Fetches detailed information for each player from the API
- Updates player names and positions
- Uses a 0.2-second delay between requests to avoid overwhelming the API

**Example:**
```bash
php artisan euroleague:sync-players
```

**Output:**
```
Syncing existing players from Euroleague API...

Player sync completed!
+---------+-------+
| Status  | Count |
+---------+-------+
| Updated | 245   |
| Failed  | 15    |
| Skipped | 3     |
+---------+-------+
```

**Notes:**
- Only works for players that already exist in the database (from game stats)
- Skips players without a valid player_id
- Failed requests are logged but don't stop the process

---

### 2. Fetch Game Stats (Primary Method)

Fetches detailed game statistics including player and team stats from the legacy Boxscore API.

```bash
php artisan euroleague:fetch-stats [season] [--start=X] [--end=Y] [--game=X] [--force]
```

**Arguments:**
- `season` (optional): Season code (default: E2025)

**Options:**
- `--start`: Starting game code (default: 1)
- `--end`: Ending game code (default: 100)
- `--game`: Fetch a specific game code
- `--force`: Force reload existing games (overwrites all stats)

**Examples:**
```bash
# Fetch games 1-97
php artisan euroleague:fetch-stats --start=1 --end=97

# Fetch specific game
php artisan euroleague:fetch-stats --game=50

# Force reload all games (overwrites existing data)
php artisan euroleague:fetch-stats --start=1 --end=97 --force
```

**What it does:**
- Fetches complete game statistics from the Boxscore API
- Creates game, team, and player records
- Stores detailed stats for each player (points, rebounds, assists, etc.)
- Automatically attempts to fetch player positions from the API
- Uses a 0.5-second delay between requests

---

## Service Methods

The `EuroleagueStatsService` provides the following public methods:

### Player Methods

#### `fetchPlayerDetails(string $playerCode): ?array`
Fetches detailed information for a specific player from the API.

```php
$service = app(EuroleagueStatsService::class);
$playerData = $service->fetchPlayerDetails('P004840');
// Returns player data array or null if not found
```

#### `updatePlayerFromApi(string $playerCode): bool`
Fetches and updates a specific player's details in the database.

```php
$success = $service->updatePlayerFromApi('P004840');
// Returns true if successful, false otherwise
```

#### `syncExistingPlayers(): array`
Syncs all existing players in the database by fetching their details from the API.

```php
$results = $service->syncExistingPlayers();
// ['updated' => 245, 'failed' => 15, 'skipped' => 3]
```

---

### Game Methods

#### `fetchGameDetails(int $gameCode, string $seasonCode = 'E2025'): ?array`
Fetches detailed information for a specific game from the v1 API.

```php
$gameData = $service->fetchGameDetails(50, 'E2025');
// Returns game data array or null if not found
```

#### `fetchAndStoreGame(int $gameCode, string $seasonCode, bool $force = false): bool`
Fetches complete game statistics from the Boxscore API and stores them in the database.

```php
// Fetch game 50
$success = $service->fetchAndStoreGame(50, 'E2025');

// Force reload game 50 (overwrites existing stats)
$success = $service->fetchAndStoreGame(50, 'E2025', true);
```

---

## Data Flow

### Complete Data Sync Workflow

To fully populate your database with Euroleague data:

1. **Fetch Game Stats** (creates games, teams, and players with stats)
   ```bash
   php artisan euroleague:fetch-stats --start=1 --end=100
   ```

2. **Sync Player Details** (optional - enriches player data)
   ```bash
   php artisan euroleague:sync-players
   ```

### Update Existing Data

To update/refresh data:

```bash
# Force reload game stats (overwrites all stats)
php artisan euroleague:fetch-stats --start=1 --end=97 --force

# Refresh player details
php artisan euroleague:sync-players
```

---

## Position Normalization

Player positions are normalized to three standard values:
- **G** (Guard): PG, SG, Point Guard, Shooting Guard
- **F** (Forward): SF, PF, Small Forward, Power Forward
- **C** (Center): C, Centre

The service automatically normalizes positions from various API formats.

---

## API Response Handling

### Player API Response (v1)
Expected fields (may vary):
- `personId` / `playerId` / `id`: Player identifier
- `name` / `playerName`: Player full name
- `position` / `pos`: Player position

### Game API Response (Boxscore)
Complete game statistics including:
- Game metadata (referees, attendance, quarters)
- Team statistics (shooting, rebounds, assists, turnovers, etc.)
- Player statistics (detailed per-player stats)
- Quarter-by-quarter scores

---

## Error Handling

All methods include comprehensive error handling:
- Failed API requests are logged and don't crash the application
- Invalid data is skipped and logged
- Results summaries show updated/failed/skipped counts
- Detailed error logs are available in `storage/logs/laravel.log`
- 404 responses from player/game lookups are handled gracefully

---

## Rate Limiting

To avoid overwhelming the API:
- Game stats fetching: 0.5-second delay between requests
- Player sync: 0.2-second delay between requests
- All requests have a 10-30 second timeout

---

## Notes

- Season codes follow the format: `E2025`, `E2024`, etc. (E = Euroleague)
- The v1 API endpoints require specific codes and don't support bulk listing
- The Boxscore API is the primary source for complete game data
- Player positions from the API may not always be available (returns 404)
- Use the `--force` flag carefully as it deletes and recreates all stats for the specified games

