# Defense vs Position (DvP) Calculation - Implementation Guide

## Overview
This document explains how the Defense vs Position (DvP) statistics are calculated in this Laravel application, matching the methodology used by EuroLeague Fantasy.

## Key Concept: **Per-Player Average**, Not Per-Game

The most important thing to understand is:

### ❌ WRONG Approach (Per Game):
```
Average = Total Points / Number of Games
```

### ✅ CORRECT Approach (Per Player):
```
Average = Sum of all qualifying players' stats / Number of qualifying players
```

Or even simpler, using SQL:
```sql
AVG(points) where minutes >= 18 and position = 'G'
```

## Example Calculation

### Scenario:
Team A played **10 games** against various opponents.

**Game 1:** 4 opponent guards played 18+ minutes
**Game 2:** 5 opponent guards played 18+ minutes  
**Game 3:** 3 opponent guards played 18+ minutes  
...  
**Total:** 42 opponent guards played 18+ minutes across all games

**Total Points Scored by All 42 Guards:** 1,020 points

### The Calculation:
```
Defense vs Guards (Points) = 1,020 / 42 = 24.3 points per guard
```

**NOT:** `1,020 / 10 = 102 points per game` ❌

## SQL Implementation

### Using Laravel Eloquent:
```php
$opponentStats = PlayerGameStat::whereIn('game_id', $gameIds)
    ->where('team_id', '!=', $team->id)
    ->where('minutes', '>=', 18)
    ->where('position', 'LIKE', "%G%")
    ->selectRaw('
        COUNT(*) as total_players,
        AVG(points) as avg_points,
        AVG(total_rebounds) as avg_rebounds,
        AVG(assists) as avg_assists,
        AVG(valuation) as avg_pir,
        AVG(steals) as avg_steals,
        AVG(blocks_favor) as avg_blocks
    ')
    ->first();
```

### Raw SQL Equivalent:
```sql
SELECT 
    COUNT(*) as total_players,
    AVG(points) as avg_points,
    AVG(total_rebounds) as avg_rebounds,
    AVG(assists) as avg_assists,
    AVG(valuation) as avg_pir,
    AVG(steals) as avg_steals,
    AVG(blocks_favor) as avg_blocks
FROM player_game_stats
WHERE game_id IN (1, 2, 3, ..., 10)  -- Games where Team A played
  AND team_id != 7                    -- Opponent players only
  AND minutes >= 18                   -- Minimum 18 minutes played
  AND position LIKE '%G%'             -- Guards only
```

## Position Filtering

We use the Position enum constants defined in `app/Enums/Position.php`:

- **G** = Guard
- **F** = Forward  
- **C** = Center

The query uses `LIKE '%G%'` to match positions like:
- "G"
- "PG" (Point Guard)
- "SG" (Shooting Guard)
- "G-F" (Guard-Forward)

## Why This Matters

### Sample Size Interpretation:
If the UI shows:
- **Team A vs Guards:** 24.3 pts | **Sample: 42**

This means:
- 42 individual guards (playing 18+ min) were evaluated
- NOT 42 games
- The average guard scores 24.3 points against Team A

### Comparing Teams:
A team that played 10 games might have:
- **42 guards** qualify (4.2 per game average)
- **38 forwards** qualify (3.8 per game average)
- **30 centers** qualify (3.0 per game average)

This variance is expected and normal.

## Controller Logic Flow

1. **Get all games** the team played in
2. **Filter opponent players** who:
   - Played in those games
   - Are NOT on this team
   - Played ≥ 18 minutes
   - Match the selected position (G, F, or C)
3. **Calculate average stats** across ALL qualifying players
4. **Display** the per-player average and total player count

## Output Structure

```php
[
    'team' => Team,
    'stats' => [
        'games_count' => 10,        // Games the team played
        'total_players' => 42,       // Qualifying opponent players
        'avg_points' => 24.3,        // Average per player
        'avg_rebounds' => 5.1,
        'avg_assists' => 3.2,
        'avg_pir' => 18.7,
        'avg_steals' => 1.1,
        'avg_blocks' => 0.3,
    ]
]
```

## Frontend Display

### Table Headers:
| Team | Points | Rebounds | Assists | PIR | Steals | Blocks | Sample |
|------|--------|----------|---------|-----|--------|--------|--------|
| Real Madrid | 24.3 | 5.1 | 3.2 | 18.7 | 1.1 | 0.3 | 42 |

### Interpretation:
- **Higher values** = Worse defense (allows more stats)
- **Lower values** = Better defense (allows fewer stats)
- **Sample** = Number of qualifying players, not games

## Sorting

Users can sort by any stat column:
- **Descending** (default): Shows worst defenses first (highest values)
- **Ascending**: Shows best defenses first (lowest values)

## Position Tabs

The UI has tabs for:
1. **Guards** - Shows DvP vs guards
2. **Forwards** - Shows DvP vs forwards
3. **Centers** - Shows DvP vs centers

Each tab recalculates with the appropriate position filter.

## References

This implementation matches the methodology described in:
- EuroLeague Fantasy: dunkest.com
- 18+ minute minimum is standard for meaningful player contributions
- Per-player averaging is the industry standard for DvP metrics

