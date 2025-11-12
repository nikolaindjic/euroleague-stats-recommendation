# Defense vs Position - ACTUAL EuroLeague Fantasy Formula

## The Confusion

I initially implemented **per-player average**, but looking at your requirements again:

> "it's the points they allowed **per game** per position"

This means:

## ❌ WRONG (What I Implemented)
```
Average = Total Points by Guards / Number of Guards
Example: 349 points / 25 guards = 13.96 PPG ← PER PLAYER
```

## ✅ CORRECT (EuroLeague Fantasy)
```
Average = Total Points by Guards / Number of Games
Example: 349 points / 9 games = 38.78 PPG ← PER GAME

BUT broken down by position (only counting guards with 18+ min)
```

## The Real Formula

For "Defense vs Guards - Points":
1. **For each game**, sum points scored by opponent guards (18+ min)
2. **Average across all games**

```
Game 1: Guard A (20 pts) + Guard B (15 pts) = 35 pts to guards
Game 2: Guard C (25 pts) + Guard D (10 pts) + Guard E (18 pts) = 53 pts to guards
...
Game 9: Guard X (12 pts) + Guard Y (8 pts) = 20 pts to guards

Total: 349 points to guards across 9 games
Average: 349 / 9 = 38.78 points per game allowed to guards
```

## This Makes Sense Because:

A team wants to know: **"How many points do guards score against us per game?"**
- Not: "What does the average guard score?" (that's what I calculated)
- But: "What's the total guard production we allow per game?"

## Implementation Fix

Need to change from:
```php
AVG(points) as avg_points  // Per player
```

To:
```php
// Group by game first, then average across games
SELECT game_id, SUM(points) as game_total
FROM player_game_stats
WHERE position LIKE '%G%' AND minutes >= 18
GROUP BY game_id

Then: AVG(game_total) across all games
```

## Example Comparison

**Team A played 10 games, 42 guards qualified (18+ min)**

### My Current (Wrong) Calculation:
- Total: 1,020 points by guards
- Result: 1,020 / 42 = **24.3 points per guard**

### Correct (EuroLeague Fantasy):
- Total: 1,020 points by guards  
- Result: 1,020 / 10 = **102 points per game to guards**

The second one answers: "How many points do guards score against this team per game?"

## This Explains Why Numbers Seem Off!

You're comparing:
- **Your calculation**: Per-player average (small numbers like 13.96)
- **EuroLeague Fantasy**: Per-game total to position (bigger numbers like 38.78)

Need to fix the SQL query to sum by game first, then average!

