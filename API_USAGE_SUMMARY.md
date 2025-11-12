# Euroleague API Integration - Quick Summary

## What Was Implemented

I've explored the Euroleague API (https://api-live.euroleague.net/swagger/index.html) for fetching player data and schedule information.

### Key Findings ⚠️

The Euroleague API v1 endpoints have significant limitations:

1. **Individual lookups only** - No bulk data retrieval:
   - `/v1/players` requires a specific `PlayerCode` parameter
   - `/v1/games` requires specific `gameCode` and `seasonCode` parameters
   - **No endpoints exist to list all players or all games**

2. **Incompatible Player Codes** ⚠️:
   - The **Boxscore API** uses player codes like `P004840`, `P011927`, etc.
   - The **v1 API** uses **different player codes** (incompatible format)
   - You **cannot** use Boxscore player IDs to query the v1 API
   - There's no mapping available between the two systems

### Recommendation

**Continue using the legacy Boxscore API** (`https://live.euroleague.net/api/Boxscore`) as your primary data source:
- ✅ Provides complete game statistics
- ✅ Includes all player and team data
- ✅ Works with consistent player IDs
- ✅ No compatibility issues

---

## New Features Added

### Service Methods (`EuroleagueStatsService`)

⚠️ **Note**: These methods are provided for reference but **cannot be used** with existing database player IDs due to incompatible player code formats between Boxscore API and v1 API.

#### Player Methods
- **`fetchPlayerDetails(string $playerCode)`** - Fetch specific player's details from v1 API
- **`updatePlayerFromApi(string $playerCode)`** - Update a specific player in the database
- **`syncExistingPlayers()`** - ⚠️ **Will not work** - Tries to sync using incompatible player codes

#### Game Methods
- **`fetchGameDetails(int $gameCode, string $seasonCode)`** - Fetch specific game details from v1 API

### Console Command

⚠️ **This command will not work** with existing data:

```bash
php artisan euroleague:sync-players
```

This command attempts to fetch player details from the v1 API using player codes from the Boxscore API, but they use incompatible ID formats.

---

## How To Use

### Recommended Workflow (Boxscore API Only)

**Step 1: Fetch Game Data** - This is your complete solution:

```bash
# Fetch all available games (creates games, teams, players with full stats)
php artisan euroleague:fetch-stats --start=1 --end=100

# Force reload existing games
php artisan euroleague:fetch-stats --start=1 --end=97 --force
```

This single command provides:
- ✅ Complete game statistics
- ✅ Team data
- ✅ Player data with positions (where available)
- ✅ All detailed stats (points, rebounds, assists, etc.)

**That's it!** The Boxscore API provides everything you need. The v1 API endpoints are not compatible with this data.

---

## Why Not Use v1 API?

The v1 API endpoints have these limitations:

❌ **Player codes are incompatible**
- Boxscore API uses codes like: `P004840`, `P011927`, `P009299`
- v1 API uses different codes (format unknown without documentation)
- No conversion/mapping available

❌ **No bulk endpoints**
- Can only query individual players/games
- Would require knowing v1 API's player codes in advance

❌ **Provides less data**
- v1 API returns basic player info
- Boxscore API returns complete game statistics

### Conclusion

✅ **Use Boxscore API exclusively** - It provides all the data you need with consistent player IDs.

---

## API Endpoints Reference

### Working Endpoints

✅ **Boxscore API** (Primary data source)
```
GET https://live.euroleague.net/api/Boxscore?gamecode={code}&seasoncode={season}
```
Returns: Complete game statistics, team data, player stats

✅ **Player Details API**
```
GET https://api-live.euroleague.net/v1/players?PlayerCode={playerCode}
```
Returns: Individual player details (name, position, etc.)

✅ **Game Details API**
```
GET https://api-live.euroleague.net/v1/games?gameCode={code}&seasonCode={season}
```
Returns: Individual game details

### Non-Working Endpoints (For Bulk Data)

❌ **List All Players** - No such endpoint exists
❌ **List All Games** - No such endpoint exists

---

## Example Workflow

```bash
# Step 1: Fetch game statistics for the season
php artisan euroleague:fetch-stats --start=1 --end=97

# That's it! Now view your data:
# - Visit /games to see all games
# - Visit /players to see all players
# - Visit /teams to see all teams
# - Visit /stats-vs-position for defense analysis
# - Visit /form-recommendations for player recommendations
```

### Update Data

```bash
# Reload specific games to get latest stats
php artisan euroleague:fetch-stats --start=90 --end=97 --force
```

---

## Rate Limiting

To protect the API:
- **Game stats fetching**: 0.5 second delay between requests
- **Player sync**: 0.2 second delay between requests
- **Timeout**: 10-30 seconds per request

---

## Position Normalization

All positions are normalized to:
- **G** (Guard)
- **F** (Forward)
- **C** (Center)

---

## Documentation

Full documentation available in:
- `API_INTEGRATION.md` - Complete API integration guide
- `SETUP.md` - Initial setup instructions
- This file - Quick reference

---

## Notes

1. The v1 API is useful for enriching individual records but not for bulk imports
2. The Boxscore API remains the best source for complete game data
3. Player positions from the v1 API may return 404 (not available for all players)
4. All errors are logged to `storage/logs/laravel.log`
5. Make sure your MySQL/database server is running before using commands

---

## Troubleshooting

**Database connection error:**
```
SQLSTATE[HY000] [2002] No connection could be made...
```
Solution: Start your MySQL/database server (e.g., via Laragon)

**API returns 404 for player:**
- Normal behavior - not all players have detailed API data
- Player will still exist in database from game stats

**API returns 400:**
- Check that you're using the correct parameter names
- Individual endpoints require specific codes (not bulk queries)

