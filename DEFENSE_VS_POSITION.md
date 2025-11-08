# Defense vs Position Feature

## ✅ Feature Complete!

A new analytics page that shows how much each team allows to different positions (Guards, Forwards, Centers).

## 📊 What It Shows

### Defensive Performance Metrics
For each team, the page displays:
- **Points Per Game (PPG)** - Average points allowed to opponent position players
- **Rebounds Per Game (RPG)** - Average rebounds allowed
- **Assists Per Game (APG)** - Average assists allowed
- **Performance Index Rating (PIR)** - Overall efficiency allowed
- **Steals Per Game (SPG)** - Steals by that position
- **Blocks Per Game (BPG)** - Blocks by that position
- **Sample Size** - Number of player performances analyzed

## 🎯 Position Categories

### Guards
- Matches players with position containing "Guard" or "G"
- Point Guards, Shooting Guards, Combo Guards

### Forwards
- Matches players with position containing "Forward" or "F"  
- Small Forwards, Power Forwards

### Centers
- Matches players with position containing "Center" or "C"
- Traditional Centers, Center-Forwards

## 🏆 Ranking System

Teams are ranked from **best to worst** defense against each position:

- **Top 3** (Green highlight 🥇🥈🥉) - Best defensive teams
- **Bottom 3** (Red highlight) - Weakest defensive teams
- Rankings based on average points allowed (lower is better)

## 📈 Key Features

### 1. Position Tabs
Switch between Guards, Forwards, and Centers with tabbed navigation

### 2. League Averages
See 4 summary cards showing league-wide averages for comparison

### 3. Variance Indicators
Each team's PPG shows how far above/below league average:
- 🟢 Green = Below average (good defense)
- 🔴 Red = Above average (weak defense)

### 4. Sticky Headers
Table headers and team columns stay visible when scrolling

### 5. Dark Mode Support
Full support for light and dark themes

## 🔧 Technical Implementation

### Database Changes
- Added `position` column to `player_game_stats` table
- Position data is now captured from Euroleague API

### New Files
- `resources/views/stats/vs-position.blade.php` - Main view
- `database/migrations/2025_11_08_151223_add_position_to_player_game_stats_table.php`

### Updated Files
- `routes/web.php` - Added route
- `app/Http/Controllers/StatsController.php` - Added `statsVsPosition()` method
- `app/Services/EuroleagueStatsService.php` - Captures position data
- `app/Models/PlayerGameStat.php` - Added position to fillable
- `resources/views/layouts/main.blade.php` - Added nav link

## 📖 How It Works

1. **Data Collection**: For each team, find all games they played
2. **Opponent Stats**: Get stats from opponent players in those games
3. **Position Filtering**: Filter by selected position (Guard/Forward/Center)
4. **Aggregation**: Calculate averages across all performances
5. **Ranking**: Sort teams by points allowed (ascending)

## 🎨 Visual Design

- Color-coded rankings (green = good, red = bad)
- Medal emojis for top 3 teams
- Position emojis in tabs (🏀 Guards, 🏃 Forwards, 🏋️ Centers)
- Responsive table with horizontal scroll on mobile
- Tooltips and helpful legends

## 📱 Access

**URL**: `/stats-vs-position`

**Navigation**: 
- Desktop: Top navigation bar → "Defense vs Position"
- Mobile: Mobile menu → "Defense vs Position"

## 💡 Use Cases

### Scouting
- Identify which teams struggle against specific positions
- Find matchup advantages before games

### Analysis
- Compare defensive strategies across teams
- Track how teams perform against different play styles

### Fantasy/Betting
- Predict high-scoring games based on defensive weaknesses
- Target players in favorable matchups

## 🔄 Data Requirements

**Note**: Position data needs to be populated by re-fetching games. The position field comes from the Euroleague API's player data.

To populate position data for existing games:
```bash
php artisan euroleague:fetch-stats --start=1 --end=90
```

This will update all games with position information.

## 📊 Example Insights

- "Team X allows 18.5 PPG to Guards (2.3 above league avg)"
- "Team Y is ranked #1 vs Centers with only 12.1 PPG allowed"
- "Team Z's defense is strongest against Forwards"

---

**Status**: ✅ Fully functional with dark mode support and responsive design!

