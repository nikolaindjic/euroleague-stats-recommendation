# Quick Start: Schedule Integration

## What You Need to Know

The schedule integration is **complete and working**! ✅

## Run This First

```bash
# Run the migration (adds new fields to games table)
php artisan migrate
```

## Then Use It

```bash
# Sync the complete schedule
php artisan euroleague:sync-schedule

# It will show you:
# 1. How many games were synced
# 2. Next upcoming games
# 3. Which played games need stats

# Then fetch stats for specific games it identifies:
php artisan euroleague:fetch-stats --game=231
```

## That's It!

The command will guide you through everything else.

For full documentation, see:
- **SCHEDULE_INTEGRATION_SUCCESS.md** - Complete guide
- **SCHEDULE_COMPLETE.md** - Technical summary

## Example

```bash
$ php artisan euroleague:sync-schedule

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
+------+-------+------------------+------------+------------+
| Game | Round | Date             | Home       | Away       |
+------+-------+------------------+------------+------------+
| 231  | 24    | 2025-01-15 20:00 | Barcelona  | Real Madrid|
| 232  | 24    | 2025-01-15 21:00 | Monaco     | Fenerbahce |
+------+-------+------------------+------------+------------+

5 played games need stats fetched:
+------+-------+-------------+-------------+
| Game | Round | Home        | Away        |
+------+-------+-------------+-------------+
| 226  | 23    | Real Madrid | Olympiacos  |
| 227  | 23    | Barcelona   | Monaco      |
+------+-------+-------------+-------------+

To fetch stats for these games, run:
php artisan euroleague:fetch-stats --game=<GAME_CODE>
```

Now you know exactly which games to fetch!

## Benefits

✅ See complete schedule
✅ Know upcoming games  
✅ Identify which games need stats
✅ No more guessing game codes
✅ Efficient data fetching

**Enjoy!** 🎉

