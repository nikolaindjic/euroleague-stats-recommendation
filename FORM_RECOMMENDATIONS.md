# Form & Matchup Recommendations - Feature Documentation

## 🎯 Overview

The Form & Matchup Recommendations page is an advanced analytics tool that helps identify the best fantasy basketball picks by combining two key metrics:

1. **Player Form** - Recent performance (last 3 games)
2. **Opponent Matchup Quality** - How well/poorly opponents defend against that position

## 📊 How It Works

### The XY Scatter Chart

The chart plots players on two axes:

- **X-Axis (Horizontal):** Opponent Defense Quality
  - Calculated as average PIR allowed by all teams to that position
  - **Higher values** = Worse defense = **Easier matchup**
  - Based on Defense vs Position data (18+ minute threshold)

- **Y-Axis (Vertical):** Recent Form
  - Player's average PIR over last 3 games
  - **Higher values** = **Better recent performance**
  - Only includes games where player played 15+ minutes

### The Four Quadrants

```
High Form, Easy Matchup (🌟 Best Picks)     |  High Form, Tough Matchup (📈)
-------------------------------------------|-------------------------------------------
                                          |
                  TOP RIGHT               |           TOP LEFT
              (Best Fantasy Picks)        |       (Good players, hard games)
                                          |
=========================================CENTER=========================================
                                          |
                BOTTOM RIGHT              |         BOTTOM LEFT
           (Easy games, poor form)        |    (❌ Avoid - Poor form, tough games)
                                          |
Low Form, Easy Matchup (⚠️)               |  Low Form, Tough Matchup (❌ Worst)
```

## 🎨 Color Coding

Players are color-coded by position:
- **Blue dots** = Guards (G, PG, SG)
- **Green dots** = Forwards (F, SF, PF)
- **Purple dots** = Centers (C)

## 🔍 Features

### 1. Interactive Chart
- **Click any point** to view that player's full stats page
- **Hover over points** to see:
  - Player name
  - Position
  - Recent form (PIR)
  - Matchup quality
  - Recent averages (points, rebounds, assists)

### 2. Team Filter
- Filter by specific team to analyze roster options
- Dropdown shows all teams alphabetically
- "Clear Filter" button to reset

### 3. Top 10 Recommendations Table
- Automatically ranks players by combined score (form + matchup)
- Shows:
  - Rank with medals (🥇🥈🥉) for top 3
  - Player name (clickable link)
  - Position badge
  - Recent form with game count
  - Matchup quality (color-coded: green=easy, yellow=medium, red=tough)
  - Recent averages for points, rebounds, assists

## 📐 Calculation Details

### Player Recent Form
```php
recentForm = AVG(valuation) for last 3 games where minutes >= 15
```

Example:
- Game 1: 18 PIR
- Game 2: 22 PIR
- Game 3: 16 PIR
- **Result: 18.67 PIR**

### Opponent Defense Quality
```php
For each opponent team:
  opponentDefense = AVG(valuation) allowed to this position (18+ min players)

averageOpponentDefense = AVG(all opponent defense scores)
```

Example for a Guard:
- Team A allows: 12.5 PIR to guards
- Team B allows: 14.2 PIR to guards
- Team C allows: 11.8 PIR to guards
- ...
- **Result: 12.83 PIR average** (across all potential opponents)

### Combined Score (for ranking)
```php
combinedScore = recentForm + opponentQuality
```

Higher combined score = Better pick

## 🎯 How to Use

### For Daily Fantasy Sports (DFS)

1. **Visit the Recommendations page**
2. **Look at top-right quadrant** for best value
3. **Check Top 10 table** for quick picks
4. **Filter by team** if analyzing specific matchup
5. **Click players** to see detailed stats

### For Season-Long Fantasy

1. **Identify trending players** (moving up vertically = improving form)
2. **Find schedule advantages** (teams with easier average matchups)
3. **Spot buy-low candidates** (bottom-right = good matchup, can bounce back)

### For Betting

1. **Over/Under picks** - Top-right players likely to exceed projections
2. **Prop bets** - Use recent stats to identify value
3. **Team totals** - Filter by team to see overall roster health

## 🔧 Technical Details

### Data Requirements

**Minimum data needed per player:**
- At least 2 recent games with 15+ minutes
- Position data must be populated
- Recent game stats (last 3 games)

**Database queries:**
- Players with recent games: `Player::whereHas('gameStats')`
- Recent stats: Latest 3 games ordered by game_id DESC
- Defense data: Pulled from existing Defense vs Position calculations

### Performance Notes

The page may take a few seconds to load on first visit as it:
1. Queries all players with recent games
2. Calculates form for each player (3 games)
3. Calculates average opponent defense (queries all teams)
4. Generates chart data for rendering

**Optimization tips:**
- Filter by team for faster load times
- Results could be cached for 1 hour (future enhancement)
- Defense vs Position data could be pre-calculated

## 📱 Responsive Design

- **Desktop:** Full chart with navigation
- **Mobile:** Chart adapts to screen size, touch-friendly points
- **Dark mode:** Fully supported with appropriate colors

## 🚀 Future Enhancements

Possible improvements:
1. **Next game opponent** - Show specific next opponent instead of average
2. **Adjustable form window** - Choose 1, 3, 5, or 10 game averages
3. **Position filter** - Filter chart by Guards, Forwards, or Centers
4. **Export data** - Download recommendations as CSV
5. **Trending indicators** - Show players improving/declining
6. **Historical comparison** - Compare current form to season average
7. **Minutes trend** - Flag players with increasing/decreasing playing time

## 💡 Tips for Best Results

1. **Use in combination** with Defense vs Position page
2. **Check player recent games** (click player name) for context
3. **Consider sample size** - 3 games is small, use with caution
4. **Factor in injuries** - Page doesn't show injury status
5. **Schedule matters** - Check actual upcoming opponents
6. **Minutes trends** - Verify playing time is stable

## 🎓 Example Use Case

**Scenario:** You're setting your fantasy lineup for this week.

**Steps:**
1. Navigate to Form & Matchup Recommendations
2. See **Player X** in top-right: 20 PIR form, 14.5 matchup quality
3. Click Player X to verify:
   - Last 3 games: 22, 20, 18 PIR ✓ Consistent
   - Position: Guard ✓
   - Minutes: 28, 30, 27 ✓ Starter role secure
4. Check Defense vs Position:
   - Upcoming opponent allows 14.5 PIR to guards ✓ Confirms easy matchup
5. **Decision:** Add Player X to starting lineup with high confidence

## 📚 Related Pages

- **Defense vs Position** - Detailed breakdown by team and position
- **Players** - Full player statistics and game logs
- **Teams** - Team-level analytics
- **Games** - Individual game box scores

## ❓ FAQ

**Q: Why do some players not appear?**
A: Players need at least 2 recent games with 15+ minutes and position data.

**Q: Why is the matchup quality the same for all players?**
A: Currently showing average across all opponents. Future update will show specific next opponent.

**Q: How often is data updated?**
A: Real-time - reflects latest game stats in database.

**Q: Can I see more than top 10?**
A: Yes, all qualifying players appear on the chart. Click any point to see details.

**Q: What if a player changed position recently?**
A: Uses position from most recent game.

---

**Last Updated:** Created with initial release
**Version:** 1.0

