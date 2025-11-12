# Force Reload Game Stats

## Overview
The `--force` option allows you to reload existing games and completely refresh all their stats from the API.

## What Force Mode Does

When you use the `--force` flag:

1. **Deletes all existing stats** for each game being reloaded:
   - All `TeamGameStat` records for that game
   - All `PlayerGameStat` records for that game

2. **Fetches fresh data** from the Euroleague API

3. **Recreates all stats** with the latest data from the API

4. **Updates the game record** with any changed metadata (referees, attendance, etc.)

## Usage Examples

### Reload a single game
```bash
php artisan euroleague:fetch-stats E2025 --game=45 --force
```

### Reload a range of games
```bash
php artisan euroleague:fetch-stats E2025 --start=1 --end=97 --force
```

### Reload all games from 1 to 97
```bash
php artisan euroleague:fetch-stats E2025 --start=1 --end=97 --force
```

### Normal mode (skip existing games)
```bash
php artisan euroleague:fetch-stats E2025 --start=1 --end=100
```

## When to Use Force Mode

- **Stats corrections**: When Euroleague updates stats after a game
- **Position data updates**: When player positions have been corrected
- **Data inconsistencies**: When you notice incorrect or missing data
- **Fresh reload**: When you want to ensure all data is up-to-date

## Technical Details

### Without Force Mode
- Skips games that already exist in the database
- Only fetches new games
- Uses `updateOrCreate` which updates existing records

### With Force Mode
- Processes all games in the range, regardless of existence
- **Deletes** all `team_game_stats` and `player_game_stats` for each game
- **Recreates** all stats from fresh API data
- Ensures a completely clean reload

## Notes

- The game record itself is never deleted, only updated
- Team and Player records are never deleted
- Only the game-specific stats (TeamGameStat and PlayerGameStat) are deleted and recreated
- A 0.5 second delay is applied between API calls to avoid overwhelming the server
- All operations are wrapped in database transactions for data integrity

