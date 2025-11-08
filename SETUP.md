# 🏀 Euroleague Stats & Recommendation System

A Laravel-based application for fetching, storing, and displaying Euroleague basketball statistics with a clean, modern UI.

## Features

- **Automatic Data Fetching**: Fetch game data from the official Euroleague API
- **Comprehensive Statistics**: Track player and team statistics across all games
- **Modern UI**: Clean, responsive interface using TailwindCSS
- **Database Storage**: Efficiently store and query game, player, and team data
- **Detailed Views**: 
  - Games list with scores and attendance
  - Individual game box scores
  - Player career statistics
  - Team performance metrics

## Requirements

- PHP 8.2+
- Composer
- Laravel 11.x
- SQLite (or any Laravel-supported database)

## Installation

1. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Set up environment** (if not already done)
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **Run migrations**
   ```bash
   php artisan migrate
   ```

4. **Build assets** (optional, for production)
   ```bash
   npm run build
   ```

## Usage

### Fetching Game Data

The application includes an Artisan command to fetch game data from the Euroleague API.

**Fetch a specific game:**
```bash
php artisan euroleague:fetch-stats E2025 --game=1
```

**Fetch a range of games:**
```bash
php artisan euroleague:fetch-stats E2025 --start=1 --end=50
```

**Parameters:**
- `season`: Season code (default: E2025)
- `--game`: Fetch a specific game code
- `--start`: Starting game code (default: 1)
- `--end`: Ending game code (default: 100)

### Running the Application

```bash
php artisan serve
```

Then open your browser to `http://localhost:8000`

## Database Structure

### Tables

- **games**: Stores game information (game_code, season_code, referees, attendance)
- **teams**: Team information (team_code, team_name)
- **players**: Player information (player_id, player_name)
- **team_game_stats**: Team statistics per game (points, rebounds, assists, etc.)
- **player_game_stats**: Player statistics per game (detailed box score stats)

## Views Available

- `/games` - List of all games
- `/games/{id}` - Detailed game view with box scores
- `/teams` - List of all teams
- `/teams/{id}` - Team statistics and recent games
- `/players` - List of all players
- `/players/{id}` - Player career statistics and recent games

## API Reference

The application fetches data from:
```
https://live.euroleague.net/api/Boxscore?gamecode={game_code}&seasoncode={season_code}
```

## Project Structure

```
app/
├── Console/Commands/
│   └── FetchEuroleagueStats.php    # Artisan command for fetching data
├── Http/Controllers/
│   └── StatsController.php         # Main stats controller
├── Models/
│   ├── Game.php
│   ├── Team.php
│   ├── Player.php
│   ├── TeamGameStat.php
│   └── PlayerGameStat.php
└── Services/
    └── EuroleagueStatsService.php  # API integration service

database/migrations/
├── 2025_11_08_000001_create_games_table.php
├── 2025_11_08_000002_create_teams_table.php
├── 2025_11_08_000003_create_players_table.php
├── 2025_11_08_000004_create_team_game_stats_table.php
└── 2025_11_08_000005_create_player_game_stats_table.php

resources/views/
├── layouts/
│   └── main.blade.php              # Main layout template
└── stats/
    ├── index.blade.php             # Games list
    ├── game.blade.php              # Game details
    ├── teams.blade.php             # Teams list
    ├── team.blade.php              # Team details
    ├── players.blade.php           # Players list
    └── player.blade.php            # Player details
```

## Statistics Tracked

### Player Stats
- Points, Field Goals (2PT & 3PT), Free Throws
- Rebounds (Offensive, Defensive, Total)
- Assists, Steals, Turnovers
- Blocks (For & Against)
- Fouls (Committed & Received)
- Plus/Minus, Valuation
- Minutes Played

### Team Stats
- Quarter-by-quarter scores
- Total points
- Field Goal percentages
- Rebounds and Assists
- Team totals for all player stats

## Example Workflow

1. **Run migrations:**
   ```bash
   php artisan migrate
   ```

2. **Fetch some game data:**
   ```bash
   php artisan euroleague:fetch-stats E2025 --start=1 --end=10
   ```

3. **Start the server:**
   ```bash
   php artisan serve
   ```

4. **Visit the application:**
   - Open `http://localhost:8000/games` to see all games
   - Click on any game to view detailed box scores
   - Navigate to Teams or Players to see their statistics

## Future Enhancements

- Player comparison tools
- Advanced analytics and charts
- Game predictions using ML
- Fantasy basketball recommendations
- Historical trends analysis

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

