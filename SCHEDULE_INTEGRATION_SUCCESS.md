# Schedule Integration - WORKING SOLUTION ✅

## Summary

Successfully integrated the Euroleague v2 API to fetch and sync the complete game schedule!

## What Works ✅

### 1. Schedule API (v2)
**Endpoint**: `https://api-live.euroleague.net/v2/competitions/E/seasons/E2024/games`

- ✅ Returns complete schedule in JSON format
- ✅ Includes all games (330 games for E2024 season)
- ✅ Has game codes, rounds, teams, scores, dates
- ✅ Indicates which games are played vs upcoming
- ✅ Works perfectly!

### 2. New Command
```bash
php artisan euroleague:sync-schedule [season]
```

**What it does:**
- Fetches complete schedule from v2 API
- Creates/updates all games in database
- Creates team records
- Shows upcoming games
- Shows which played games need stats fetched
- Syncs scores and dates

### 3. New Service Methods

**`fetchCompleteSchedule(string $seasonCode)`**
- Fetches all games for a season from v2 API
- Returns array of game data

**`syncScheduleFromV2Api(string $seasonCode)`**
- Syncs all games to database
- Creates teams
- Saves game dates, scores, team codes
- Returns created/updated/skipped counts

**`getUpcomingGames(string $seasonCode, int $limit)`**
- Returns list of upcoming (not yet played) games
- Sorted by date

**`getNextGamesToFetch(string $seasonCode, int $limit)`**
- Returns list of played games that need stats fetched
- Helps identify which games to run `fetch-stats` for

---

## Usage

### 1. Sync the Schedule
```bash
# Sync current season (E2024)
php artisan euroleague:sync-schedule

# Sync specific season
php artisan euroleague:sync-schedule E2025
```

**Output Example:**
```
Syncing schedule for season E2024...

Schedule sync completed!
+---------+-------+
| Status  | Count |
+---------+-------+
| Created | 250   |
| Updated | 80    |
| Skipped | 0     |
| Failed  | 0     |
+---------+-------+

Next 5 upcoming games:
+------+-------+------------------+----------------+----------------+
| Game | Round | Date             | Home           | Away           |
+------+-------+------------------+----------------+----------------+
| 231  | 24    | 2025-01-15 20:00 | Barcelona      | Real Madrid    |
| 232  | 24    | 2025-01-15 21:00 | Panathinaikos  | Olympiacos     |
+------+-------+------------------+----------------+----------------+

10 played games need stats fetched:
+------+-------+----------------+----------------+
| Game | Round | Home           | Away           |
+------+-------+----------------+----------------+
| 220  | 23    | Monaco         | Baskonia       |
| 221  | 23    | Fenerbahce     | Zalgiris       |
+------+-------+----------------+----------------+

To fetch stats for these games, run:
php artisan euroleague:fetch-stats --game=<GAME_CODE>
```

### 2. Fetch Stats for Specific Games
```bash
# Fetch stats for a specific game from the schedule
php artisan euroleague:fetch-stats --game=220

# Or fetch a range
php artisan euroleague:fetch-stats --start=220 --end=230
```

---

## Complete Workflow

### Initial Setup (First Time)
```bash
# Step 1: Sync the complete schedule
php artisan euroleague:sync-schedule E2024

# Step 2: Fetch stats for all played games
php artisan euroleague:fetch-stats --start=1 --end=230

# Done! Your database now has:
# - Complete schedule (upcoming games too)
# - Complete stats for all played games
# - All teams and players
```

### Regular Updates (Daily/Weekly)
```bash
# Step 1: Sync schedule (gets latest games and scores)
php artisan euroleague:sync-schedule

# Step 2: The command will tell you which games need stats
# Then fetch those specific games:
php artisan euroleague:fetch-stats --game=231
php artisan euroleague:fetch-stats --game=232
```

---

## Database Changes

### New Migration
Added fields to `games` table:
- `game_date` - When the game is/was played
- `home_team_code` - Home team code
- `away_team_code` - Away team code  
- `home_score` - Home team final score
- `away_score` - Away team final score
- `is_played` - Whether game has been played

### Run Migration
```bash
php artisan migrate
```

---

## API Details

### Schedule API Response Structure
```json
{
  "data": [
    {
      "gameCode": 330,
      "round": 43,
      "played": 1,
      "date": "2025-05-25T19:00:00",
      "local": {
        "club": {
          "code": "MCO",
          "name": "AS Monaco",
          "abbreviatedName": "Monaco"
        },
        "score": 70
      },
      "road": {
        "club": {
          "code": "ULK",
          "name": "Fenerbahce Beko Istanbul",
          "abbreviatedName": "Fenerbahce"
        },
        "score": 81
      },
      "audience": 12000
    }
  ],
  "total": 330
}
```

### What We Get
- ✅ Game codes (to fetch detailed stats)
- ✅ Round numbers
- ✅ Played status (0 = upcoming, 1 = played)
- ✅ Game dates/times
- ✅ Home/away teams with codes
- ✅ Final scores (for played games)
- ✅ Attendance

---

## Features

### 1. Schedule Tracking
- See all upcoming games
- Know game dates and times
- Track which teams are playing

### 2. Smart Stats Fetching
- Automatically identify which games need stats
- Don't re-fetch games that already have stats
- Only fetch played games

### 3. Team Management
- Automatically creates teams from schedule
- Uses official team codes
- Links games to teams

### 4. Complete Season View
- See entire season at a glance
- Track progress through the season
- Plan data fetching efficiently

---

## Comparison: What Changed

### Before
- ❌ No schedule visibility
- ❌ Had to guess which game codes to fetch
- ❌ Couldn't see upcoming games
- ❌ Trial and error to find valid game codes

### After  
- ✅ Complete schedule visibility
- ✅ Know exactly which games to fetch
- ✅ See all upcoming games
- ✅ Smart sync - only fetch what's needed

---

## Notes

1. **Season Code**: Use `E2024` for 2024-25 season, `E2025` for next season, etc.

2. **Game Codes**: The schedule provides all valid game codes (1-330 for a full season)

3. **Efficiency**: The sync command shows you exactly which games need stats, so you don't waste time fetching games that don't exist or already have data

4. **Upcoming Games**: You can see what games are coming up and plan to fetch their stats after they're played

5. **Scores**: The schedule includes final scores, so you can see results even before fetching detailed stats

---

## Example Use Cases

### Use Case 1: Daily Update
```bash
# Morning routine - check for new games
php artisan euroleague:sync-schedule

# Command shows: "Game 231 needs stats"
php artisan euroleague:fetch-stats --game=231
```

### Use Case 2: Catching Up
```bash
# Sync schedule first
php artisan euroleague:sync-schedule

# It shows: "Games 220-230 need stats"
php artisan euroleague:fetch-stats --start=220 --end=230
```

### Use Case 3: Planning Ahead
```bash
# Sync schedule
php artisan euroleague:sync-schedule

# Output shows upcoming games for next week
# You know when to check back for new stats
```

---

## Troubleshooting

**"Season not found"**
- Make sure you're using correct season code (E2024, not E2025 for current season)
- Check the API is available

**"No games need stats"**
- All played games already have stats! ✓
- Check for upcoming games in the command output

**Database error**
- Run migrations first: `php artisan migrate`
- Make sure database is running

---

## Next Steps

1. ✅ **Done**: Schedule integration working
2. ✅ **Done**: Smart game detection
3. ✅ **Done**: Team auto-creation
4. 📝 **Optional**: Add UI page to show schedule
5. 📝 **Optional**: Auto-fetch new games daily (cron job)

---

## Success! 🎉

You now have:
- ✅ Complete schedule from official API
- ✅ Smart game syncing
- ✅ Upcoming games visibility
- ✅ Efficient stats fetching
- ✅ Full season tracking

The schedule integration is **complete and working perfectly**!

