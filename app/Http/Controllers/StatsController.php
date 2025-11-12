<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerGameStat;
use App\Models\Team;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::with(['teamStats.team']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('game_code', 'like', "%{$search}%")
                  ->orWhere('season_code', 'like', "%{$search}%")
                  ->orWhereHas('teamStats.team', function($q) use ($search) {
                      $q->where('team_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by round
        if ($request->filled('round')) {
            $query->where('round', $request->round);
        }

        $games = $query->orderBy('game_code', 'desc')->paginate(20);

        // Get all unique rounds for filter dropdown
        $rounds = Game::select('round')
            ->distinct()
            ->orderBy('round')
            ->pluck('round');

        return view('stats.index', compact('games', 'rounds'));
    }

    public function game($id)
    {
        $game = Game::with([
            'teamStats.team',
            'playerStats.player',
            'playerStats.team'
        ])->findOrFail($id);

        // Group player stats by team
        $teamStats = [];
        foreach ($game->teamStats as $teamStat) {
            $teamStats[$teamStat->team_id] = [
                'team' => $teamStat,
                'players' => $game->playerStats->where('team_id', $teamStat->team_id)->sortByDesc('points')
            ];
        }

        return view('stats.game', compact('game', 'teamStats'));
    }

    public function players(Request $request)
    {
        $query = Player::withCount('gameStats')
            ->with(['gameStats' => function($q) {
                $q->selectRaw('player_id,
                    COUNT(*) as games_played,
                    AVG(points) as avg_points,
                    AVG(assists) as avg_assists,
                    AVG(total_rebounds) as avg_rebounds,
                    SUM(points) as total_points')
                    ->groupBy('player_id');
            }])
            ->having('game_stats_count', '>', 0);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('player_name', 'like', "%{$search}%")
                  ->orWhere('player_id', 'like', "%{$search}%");
            });
        }

        $players = $query->paginate(50);

        return view('stats.players', compact('players'));
    }

    public function player($id)
    {
        $player = Player::with(['gameStats.game', 'gameStats.team'])
            ->findOrFail($id);

        $stats = $player->gameStats()
            ->selectRaw('
                COUNT(*) as games_played,
                AVG(points) as avg_points,
                AVG(assists) as avg_assists,
                AVG(total_rebounds) as avg_rebounds,
                AVG(steals) as avg_steals,
                AVG(blocks_favor) as avg_blocks,
                AVG(turnovers) as avg_turnovers,
                AVG(minutes) as avg_minutes,
                SUM(points) as total_points,
                MAX(points) as max_points
            ')
            ->first();

        $recentGames = $player->gameStats()
            ->with(['game', 'team'])
            ->orderBy('game_id', 'desc')
            ->limit(10)
            ->get();

        return view('stats.player', compact('player', 'stats', 'recentGames'));
    }

    public function teams(Request $request)
    {
        $query = Team::withCount('gameStats')
            ->with(['gameStats' => function($q) {
                $q->selectRaw('team_id,
                    COUNT(*) as games_played,
                    SUM(CASE WHEN total_points > 0 THEN 1 ELSE 0 END) as wins,
                    AVG(total_points) as avg_points_for,
                    AVG(field_goals_made_2 + field_goals_made_3) as avg_field_goals,
                    AVG(assists) as avg_assists,
                    AVG(total_rebounds) as avg_rebounds')
                    ->groupBy('team_id');
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('team_name', 'like', "%{$search}%")
                  ->orWhere('team_code', 'like', "%{$search}%");
            });
        }

        $teams = $query->paginate(30);

        return view('stats.teams', compact('teams'));
    }

    public function team($id)
    {
        $team = Team::with(['gameStats.game'])
            ->findOrFail($id);

        $stats = $team->gameStats()
            ->selectRaw('
                COUNT(*) as games_played,
                AVG(total_points) as avg_points,
                AVG(assists) as avg_assists,
                AVG(total_rebounds) as avg_rebounds,
                AVG(steals) as avg_steals,
                AVG(field_goals_made_2 + field_goals_made_3) as avg_field_goals,
                SUM(total_points) as total_points
            ')
            ->first();

        $recentGames = $team->gameStats()
            ->with('game')
            ->orderBy('game_id', 'desc')
            ->limit(10)
            ->get();

        return view('stats.team', compact('team', 'stats', 'recentGames'));
    }

    public function statsVsPosition(Request $request)
    {
        $positionFilter = $request->get('position', Position::GUARD_LABEL);
        $sortBy = $request->get('sort', 'points');
        $sortDir = $request->get('dir', 'desc');

        // Get position code for the selected category
        $positionCode = Position::code($positionFilter);

        // Get all teams
        $teams = Team::all();

        $teamStats = [];

        foreach ($teams as $team) {
            // Get games where this team played
            $gameIds = $team->gameStats()->pluck('game_id');
            $totalGames = $gameIds->count();

            // Get ALL opponent player stats at this position with 18+ minutes
            // Calculate per-player average (NOT per-game)
            $opponentStats = PlayerGameStat::whereIn('game_id', $gameIds)
                ->where('team_id', '!=', $team->id)
                ->where('minutes', '>=', 18)
                ->where('position', 'LIKE', "%{$positionCode}%")
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

            $totalPlayers = $opponentStats->total_players ?? 0;

            if ($totalGames > 0 && $totalPlayers > 0) {
                // Stats are already averaged per player
                $teamStats[] = [
                    'team' => $team,
                    'stats' => (object)[
                        'games_count' => $totalGames,
                        'total_players' => $totalPlayers,
                        'avg_points' => $opponentStats->avg_points ?? 0,
                        'avg_rebounds' => $opponentStats->avg_rebounds ?? 0,
                        'avg_assists' => $opponentStats->avg_assists ?? 0,
                        'avg_pir' => $opponentStats->avg_pir ?? 0,
                        'avg_steals' => $opponentStats->avg_steals ?? 0,
                        'avg_blocks' => $opponentStats->avg_blocks ?? 0,
                    ]
                ];
            }
        }

        // Sort by selected stat
        $sortField = 'avg_' . $sortBy;
        usort($teamStats, function($a, $b) use ($sortField, $sortDir) {
            $valueA = $a['stats']->$sortField ?? 0;
            $valueB = $b['stats']->$sortField ?? 0;
            return $sortDir === 'asc' ? $valueA <=> $valueB : $valueB <=> $valueA;
        });

        // Get all position labels for the filter
        $positionLabels = Position::labels();

        return view('stats.vs-position', compact('teamStats', 'positionFilter', 'positionLabels', 'sortBy', 'sortDir'));
    }

    public function formRecommendations(Request $request)
    {
        $teamFilter = $request->get('team');
        $recentGames = 3; // Last 3 games for form

        // Create cache key based on team filter
        $cacheKey = 'form_recommendations_' . ($teamFilter ?? 'all');

        // Cache for 1 hour (3600 seconds)
        $playerData = cache()->remember($cacheKey, 3600, function () use ($teamFilter, $recentGames) {
            // Get all players with recent games
            $playersQuery = Player::with(['gameStats' => function($q) use ($recentGames) {
                $q->orderBy('game_id', 'desc')
                  ->limit($recentGames);
            }])->whereHas('gameStats', function($q) use ($recentGames) {
                $q->where('minutes', '>=', 15);
            });

            // Apply team filter if selected
            if ($teamFilter) {
                $playersQuery->whereHas('gameStats', function($q) use ($teamFilter) {
                    $q->where('team_id', $teamFilter);
                });
            }

            $players = $playersQuery->get();

            $data = [];

            foreach ($players as $player) {
                $recentStats = $player->gameStats()
                    ->where('minutes', '>=', 15)
                    ->orderBy('game_id', 'desc')
                    ->limit($recentGames)
                    ->get();

                if ($recentStats->count() < 2) {
                    continue; // Need at least 2 games
                }

                // Calculate player's recent form (average PIR in last N games)
                $recentAvgPir = $recentStats->avg('valuation');
                $recentAvgPoints = $recentStats->avg('points');
                $recentAvgRebounds = $recentStats->avg('total_rebounds');
                $recentAvgAssists = $recentStats->avg('assists');

                // Get player's position and team from most recent game
                $latestGame = $recentStats->first();
                if (!$latestGame || !$latestGame->position) {
                    continue;
                }

                $playerPosition = $latestGame->position;
                $playerTeamId = $latestGame->team_id;

                // Calculate average defense quality against this position across all opponents
                $positionCode = substr($playerPosition, 0, 1); // G, F, or C

                // Get all teams' defense vs this position
                $teams = Team::all();
                $opponentDefenseScores = [];

                foreach ($teams as $team) {
                    if ($team->id == $playerTeamId) {
                        continue; // Skip player's own team
                    }

                    $gameIds = $team->gameStats()->pluck('game_id')->toArray();

                    if (empty($gameIds)) {
                        continue;
                    }

                    $opponentStats = PlayerGameStat::whereIn('game_id', $gameIds)
                        ->where('team_id', '!=', $team->id)
                        ->where('minutes', '>=', 18)
                        ->where('position', 'LIKE', "%{$positionCode}%")
                        ->selectRaw('AVG(valuation) as avg_pir')
                        ->first();

                    if ($opponentStats && $opponentStats->avg_pir) {
                        $opponentDefenseScores[] = $opponentStats->avg_pir;
                    }
                }

                // Average opponent defense quality (higher = worse defense = easier matchup)
                $avgOpponentDefense = !empty($opponentDefenseScores)
                    ? array_sum($opponentDefenseScores) / count($opponentDefenseScores)
                    : 0;

                // Calculate league averages for normalization (center the graph)
                static $leagueAvgPir = null;
                static $leagueAvgOpponentDefense = null;

                if ($leagueAvgPir === null) {
                    $leagueAvgPir = 12; // Will be calculated properly below
                    $leagueAvgOpponentDefense = 12; // Will be calculated properly below
                }

                $data[] = [
                    'id' => $player->id,
                    'name' => $player->player_name,
                    'position' => $playerPosition,
                    'team_id' => $playerTeamId,
                    'recent_form' => round($recentAvgPir, 2),
                    'opponent_quality' => round($avgOpponentDefense, 2),
                    'recent_points' => round($recentAvgPoints, 2),
                    'recent_rebounds' => round($recentAvgRebounds, 2),
                    'recent_assists' => round($recentAvgAssists, 2),
                    'games_played' => $recentStats->count(),
                ];
            }

            // Calculate league averages for centering the graph
            if (count($data) > 0) {
                $formValues = collect($data)->pluck('recent_form');
                $opponentValues = collect($data)->pluck('opponent_quality');

                $avgForm = $formValues->avg();
                $avgOpponent = $opponentValues->avg();

                // Calculate standard deviations for better spread
                $formStdDev = sqrt($formValues->map(function($v) use ($avgForm) {
                    return pow($v - $avgForm, 2);
                })->avg());

                $opponentStdDev = sqrt($opponentValues->map(function($v) use ($avgOpponent) {
                    return pow($v - $avgOpponent, 2);
                })->avg());

                // Prevent division by zero
                $formStdDev = $formStdDev > 0 ? $formStdDev : 1;
                $opponentStdDev = $opponentStdDev > 0 ? $opponentStdDev : 1;

                // Normalize data using z-scores (scaled to spread nicely)
                // Multiply by scaling factor to spread the points out more
                $spreadFactor = 3; // Adjust this to change spread (higher = more spread)

                foreach ($data as &$player) {
                    // Z-score normalization: (value - mean) / stddev
                    // Then multiply by spread factor for better visualization
                    $player['form_normalized'] = round(
                        (($player['recent_form'] - $avgForm) / $formStdDev) * $spreadFactor,
                        2
                    );
                    $player['opponent_normalized'] = round(
                        (($player['opponent_quality'] - $avgOpponent) / $opponentStdDev) * $spreadFactor,
                        2
                    );
                }
            }

            return $data;
        });

        // Get all teams for filter dropdown
        $teams = Team::orderBy('team_name')->get();

        return view('stats.form-recommendations', [
            'playerData' => $playerData,
            'teams' => $teams,
            'selectedTeam' => $teamFilter,
        ]);
    }
}
