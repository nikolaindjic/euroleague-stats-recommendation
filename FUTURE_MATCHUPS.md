# Future Matchups - E2025 Season

## Quick Start

```bash
# 1. Sync future games from E2025 season
php artisan euroleague:sync-schedule

# 2. View Defense vs Position page - it now shows next opponents!
# Visit: /stats-vs-position
```

## What Changed

The **Defense vs Position** page now automatically shows:
- Each team's **next opponent**
- How that opponent **defends against the selected position**
- Game date and home/away status
- Only shows teams with upcoming games

## How It Works

1. Future games are stored in the `games` table with `is_played = false`
2. The stats page finds each team's next game
3. It calculates the opponent's defensive stats vs that position
4. You see exactly how easy/hard the matchup will be

## Database Fields

Future games in `games` table:
- `game_code` - Game identifier
- `season_code` - E2025
- `game_date` - When the game will be played
- `home_team_code` / `away_team_code` - Matchup
- `is_played` - FALSE for upcoming games

## Commands

### Sync Future Games
```bash
php artisan euroleague:sync-schedule
```
Only syncs games that haven't been played yet from E2025 season.

### Remove Old Season Games
```bash
php artisan euroleague:cleanup-old-games E2024
```
Removes all games, stats, and players from E2024 season.

## Access Future Games

```php
// Get upcoming games
$futureGames = Game::where('season_code', 'E2025')
    ->where('is_played', false)
    ->orderBy('game_date')
    ->get();

// Get next game for a team
$nextGame = Game::where('season_code', 'E2025')
    ->where('is_played', false)
    ->where(function($q) use ($teamCode) {
        $q->where('home_team_code', $teamCode)
          ->orWhere('away_team_code', $teamCode);
    })
    ->orderBy('game_date')
    ->first();
```

## That's It!

No more complexity - future matchups are integrated directly into the Defense vs Position page.

