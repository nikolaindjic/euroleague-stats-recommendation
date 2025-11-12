# Defense vs Position - FINAL IMPLEMENTATION

## Formula (CONFIRMED CORRECT)

**Per-Player Average** - NOT per-game total

```
Average = AVG(stat) for all qualifying players
        = SUM(all_stats) / COUNT(all_players)
```

### Example: EA7 Milan vs Guards
- **Games played:** 9
- **Qualifying guards (18+ min):** 25 players
- **Total points scored:** 349
- **Result:** 349 / 25 = **13.96 PPG per guard**

This means: On average, each guard who plays 18+ minutes against Milan scores 13.96 points.

## This Matches EuroLeague Fantasy (~15 PPG range)

## SQL Query

```php
PlayerGameStat::whereIn('game_id', $gameIds)  // Team's games
    ->where('team_id', '!=', $team->id)       // Opponent players only
    ->where('minutes', '>=', 18)              // 18+ minutes
    ->where('position', 'LIKE', "%G%")        // Guards
    ->selectRaw('
        COUNT(*) as total_players,
        AVG(points) as avg_points,
        AVG(total_rebounds) as avg_rebounds,
        AVG(assists) as avg_assists,
        AVG(valuation) as avg_pir
    ')
    ->first();
```

## Interpretation

**Team X shows 14.5 PPG vs Guards means:**
- The average guard (playing 18+ min) scores 14.5 points against Team X
- Higher number = Easier matchup for guards
- Lower number = Tougher matchup (better defense)

## Sample Size

**EA7 Milan Example:**
- 9 games played
- 25 qualifying guards total
- ~2.78 guards per game average
- Range: 2-4 guards per game

This variance is normal - some opponents have more guards playing 18+ min than others.

## Why This Makes Sense

### ✅ Answers the Question:
"How good is this guard matchup?"

**Example:**
- Player A is a guard playing vs Milan
- Milan allows 13.96 PPG to guards
- League average is 12.5 PPG to guards
- **Result:** Favorable matchup! (+1.46 above average)

### ❌ Per-Game Total Would Be Wrong:
If we did total per game (349/9 = 38.78), it would answer:
"How many total points do guards score per game?"

But that's not useful for picking individual players!

## What Could Still Be Different?

If your numbers don't match EuroLeague Fantasy exactly, check:

1. **Different game sample** - They might have more/fewer games loaded
2. **Fantasy Points vs PIR** - They might use a custom fantasy formula
3. **Position matching** - Different position categorization (PG/SG vs just G)
4. **Minutes threshold** - Could be different (15? 20? vs 18)
5. **Rounding** - Display rounding differences

## Current Implementation Status

✅ **Formula:** Per-player average (CORRECT)  
✅ **Filtering:** 18+ minutes, opponent players only  
✅ **Position:** G, F, or C matching  
✅ **Display:** Sortable table with rankings  
✅ **Sample size:** Shows games + total players  

## Test Command

```bash
php artisan debug:dvp {teamId} {position}

# Example:
php artisan debug:dvp 7 G  # Milan vs Guards
```

Shows detailed breakdown of calculation for verification.

