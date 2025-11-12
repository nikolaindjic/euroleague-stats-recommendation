# Euroleague API Integration - Updated

## ⚠️ Critical Finding: Incompatible API Systems

After testing the Euroleague v1 API (https://api-live.euroleague.net/swagger/index.html), we discovered it uses **incompatible player codes** with the Boxscore API.

### The Problem

- **Boxscore API** (currently used) has player codes like: `P004840`, `P011927`, `P009299`
- **v1 API** requires different player codes (format unknown)
- **No mapping exists** between the two systems
- **Cannot query v1 API** with Boxscore player IDs

### The Solution

✅ **Continue using Boxscore API exclusively** - It provides everything needed:
- Complete game statistics
- Team data
- Player data with positions
- Detailed stats (points, rebounds, assists, PIR, etc.)
- Consistent player IDs across all data

---

## Working API: Boxscore (Recommended)

### Endpoint
```
GET https://live.euroleague.net/api/Boxscore?gamecode={code}&seasoncode={season}
```

### What It Provides
- ✅ Complete game data
- ✅ Team statistics
- ✅ Player statistics (all players in the game)
- ✅ Positions (where available)
- ✅ Quarter scores
- ✅ Coach information
- ✅ Referees, attendance, etc.

### Command
```bash
# Fetch games 1-97
php artisan euroleague:fetch-stats --start=1 --end=97

# Force reload (overwrites existing data)
php artisan euroleague:fetch-stats --start=1 --end=97 --force

# Fetch specific game
php artisan euroleague:fetch-stats --game=50
```

---

## Non-Working API: v1 Endpoints

### Why They Don't Work

**Player Endpoint**
```
GET https://api-live.euroleague.net/v1/players?PlayerCode={code}
```
❌ Requires different player codes than what we have in database
❌ No way to convert Boxscore codes to v1 codes
❌ No endpoint to list all players

**Game Endpoint**
```
GET https://api-live.euroleague.net/v1/games?gameCode={code}&seasonCode={season}
```
⚠️ Works for individual games but provides less data than Boxscore
⚠️ No endpoint to list all games
⚠️ Would need to know game codes in advance

### What Was Attempted

Created these methods (but they won't work with existing data):
- `fetchPlayerDetails($playerCode)` - Requires v1 API player codes
- `updatePlayerFromApi($playerCode)` - Requires v1 API player codes
- `syncExistingPlayers()` - Fails due to incompatible codes
- `fetchGameDetails($gameCode, $seasonCode)` - Works but less useful

Created this command (but it won't work):
```bash
php artisan euroleague:sync-players
```
This tries to sync players using Boxscore player codes, which the v1 API doesn't recognize.

---

## Recommended Workflow

### 1. Initial Data Load
```bash
php artisan euroleague:fetch-stats --start=1 --end=100
```

### 2. Update Latest Games
```bash
php artisan euroleague:fetch-stats --start=95 --end=100 --force
```

### 3. View Your Data
- `/games` - All games
- `/players` - All players (with stats from games)
- `/teams` - All teams
- `/stats-vs-position` - Defense vs Position analysis
- `/form-recommendations` - Player recommendations graph

---

## Position Data

Player positions are automatically fetched when available:
1. Boxscore API provides position in player stats
2. Service attempts to fetch from v1 API as fallback (usually fails due to incompatible codes)
3. Position is normalized to: **G** (Guard), **F** (Forward), **C** (Center)

Positions are stored in both:
- `players` table (player's primary position)
- `player_game_stats` table (position played in that specific game)

---

## Summary

**What Works**:
- ✅ Boxscore API - Complete game statistics
- ✅ Automatic player/team creation
- ✅ Position normalization
- ✅ All stats tracking

**What Doesn't Work**:
- ❌ v1 API player lookups (incompatible codes)
- ❌ Bulk player/game listing (no such endpoints)
- ❌ `php artisan euroleague:sync-players` command

**Recommendation**:
Use **only the Boxscore API** via `php artisan euroleague:fetch-stats` command. It provides all necessary data.

---

## Technical Details

### Player Code Formats

**Boxscore API Examples:**
```
P004840
P011927
P009299
P005504
P007029
P002676
P002329
```

**v1 API:**
- Format unknown
- Requires different codes
- No documentation on conversion
- Cannot be used with Boxscore codes

### Error Examples

When trying to use Boxscore codes with v1 API:
```
[2025-11-12 21:40:39] local.ERROR: Failed to fetch players 
{"status":400,"body":"...PlayerCode must not be empty..."}
```

### Rate Limiting

Both APIs have rate limits:
- **Boxscore**: 0.5s delay between game fetches (in `fetchGamesForSeason`)
- **v1 API**: 0.2s delay between requests (if it worked)
- All requests have 10-30s timeout

---

## Files Created

These files exist but use incompatible v1 API:
- `app/Services/EuroleagueStatsService.php` - Has v1 methods (won't work with current data)
- `app/Console/Commands/SyncPlayers.php` - Won't work due to incompatible codes
- `examples/api_usage_examples.php` - Examples for reference only

These files are accurate:
- `API_USAGE_SUMMARY.md` - Updated with warnings
- This file - Complete explanation

---

## Future Possibilities

If you ever get access to v1 API player codes:
1. You could create a mapping table between Boxscore and v1 codes
2. Then use `syncExistingPlayers()` to enrich data
3. But currently, Boxscore API provides everything needed

Until then, **stick with Boxscore API exclusively**.

