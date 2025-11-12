# Quick Start: Fetch E2025 Schedule

## Run This Command

Open your terminal (CMD or PowerShell) and run:

```bash
php artisan euroleague:sync-schedule
```

Or run the helper script:

```bash
php sync_schedule_now.php
```

## What It Does

1. Fetches all E2025 season games from the Euroleague API
2. Syncs only **future games** (not yet played)
3. Creates team records automatically
4. Stores game dates, matchups, and venue info

## Expected Output

```
Syncing future games for season E2025...
(Only games that haven't been played yet)

Future games sync completed!
+---------+-------+
| Status  | Count |
+---------+-------+
| Created | 150   |
| Skipped | 0     |
| Failed  | 0     |
+---------+-------+

📅 Upcoming games in database:
+------+-------+------------------+-------------------------+
| Game | Round | Date             | Matchup                 |
+------+-------+------------------+-------------------------+
| 101  | 11    | 2025-01-15 20:00 | Barcelona vs Real Madrid|
| 102  | 11    | 2025-01-15 21:00 | Monaco vs Panathinaikos |
| ...
+------+-------+------------------+-------------------------+

✅ X upcoming games are now in the database!
```

## After Syncing

Visit the Defense vs Position page:
- URL: `/stats-vs-position`
- You'll see each team with their next opponent
- Shows how that opponent defends against the selected position

## If It Says "No Games"

If you see no upcoming games:
1. Check you're using E2025 (not E2024)
2. All games for the season may have been played
3. Try syncing with: `php artisan euroleague:sync-schedule E2025`

## Database Check

After running, your `games` table should have records like:

```
game_code: 101
season_code: E2025
game_date: 2025-01-15 20:00:00
home_team_code: BAR
away_team_code: MAD
is_played: false
```

These are the future games that will be used for the Defense vs Position page!

## Files

- `sync_schedule_now.php` - Helper script (can run directly)
- Command: `php artisan euroleague:sync-schedule`
- Controller: `StatsController@statsVsPosition`
- View: `resources/views/stats/vs-position.blade.php`

---

**Just run the command and you're done!** The schedule will be fetched and stored automatically.

