<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerGameStat;
use App\Models\Team;
use App\Services\EuroleagueStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        // Only show completed games that have team stats
        $query = Game::with(['teamStats.team'])
            ->whereHas('teamStats'); // Only games with stats (completed games)

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

        // Get all unique rounds for filter dropdown (only from completed games)
        $rounds = Game::whereHas('teamStats')
            ->select('round')
            ->distinct()
            ->orderBy('round')
            ->pluck('round')
            ->toArray();

        return Inertia::render('Games/Index', [
            'games' => $games->through(fn ($game) => [
                'id' => $game->id,
                'game_code' => $game->game_code,
                'round' => $game->round,
                'season_code' => $game->season_code,
                'attendance' => $game->attendance,
                'team_stats' => $game->teamStats->map(fn ($stat) => [
                    'total_points' => $stat->total_points,
                    'team' => [
                        'id' => $stat->team->id,
                        'team_name' => $stat->team->team_name,
                    ]
                ])
            ]),
            'rounds' => $rounds,
            'filters' => [
                'search' => $request->search,
                'round' => $request->round,
            ]
        ]);
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
            $players = $game->playerStats
                ->where('team_id', $teamStat->team_id)
                ->sortByDesc('points')
                ->values();

            $teamStats[] = [
                'team' => [
                    'id' => $teamStat->team->id,
                    'team_name' => $teamStat->team->team_name,
                ],
                'team_stat' => [
                    'total_points' => $teamStat->total_points,
                    'quarter1' => $teamStat->quarter1,
                    'quarter2' => $teamStat->quarter2,
                    'quarter3' => $teamStat->quarter3,
                    'quarter4' => $teamStat->quarter4,
                    'field_goals_made_2' => $teamStat->field_goals_made_2,
                    'field_goals_attempted_2' => $teamStat->field_goals_attempted_2,
                    'field_goals_made_3' => $teamStat->field_goals_made_3,
                    'field_goals_attempted_3' => $teamStat->field_goals_attempted_3,
                    'free_throws_made' => $teamStat->free_throws_made,
                    'free_throws_attempted' => $teamStat->free_throws_attempted,
                    'total_rebounds' => $teamStat->total_rebounds,
                    'assists' => $teamStat->assists,
                    'steals' => $teamStat->steals,
                    'coach' => $teamStat->coach,
                ],
                'players' => $players->map(fn ($stat) => [
                    'id' => $stat->id,
                    'player_id' => $stat->player->id,
                    'player_name' => $stat->player->player_name,
                    'dorsal' => $stat->dorsal,
                    'is_starter' => $stat->is_starter,
                    'minutes' => $stat->minutes,
                    'points' => $stat->points,
                    'field_goals_made_2' => $stat->field_goals_made_2,
                    'field_goals_attempted_2' => $stat->field_goals_attempted_2,
                    'field_goals_made_3' => $stat->field_goals_made_3,
                    'field_goals_attempted_3' => $stat->field_goals_attempted_3,
                    'free_throws_made' => $stat->free_throws_made,
                    'free_throws_attempted' => $stat->free_throws_attempted,
                    'total_rebounds' => $stat->total_rebounds,
                    'assists' => $stat->assists,
                    'valuation' => $stat->valuation,
                ])->toArray(),
            ];
        }

        return Inertia::render('Games/Show', [
            'game' => [
                'id' => $game->id,
                'game_code' => $game->game_code,
                'season_code' => $game->season_code,
                'round' => $game->round,
                'attendance' => $game->attendance,
                'referees' => $game->referees,
            ],
            'teamStats' => $teamStats
        ]);
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

        return Inertia::render('Players/Index', [
            'players' => $players,
            'filters' => [
                'search' => $request->search,
            ]
        ]);
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

        return Inertia::render('Players/Show', [
            'player' => [
                'id' => $player->id,
                'player_id' => $player->player_id,
                'player_name' => $player->player_name,
            ],
            'stats' => $stats,
            'recentGames' => $recentGames->map(fn ($stat) => [
                'id' => $stat->id,
                'game_id' => $stat->game->id,
                'game' => [
                    'game_code' => $stat->game->game_code,
                    'season_code' => $stat->game->season_code,
                ],
                'team' => [
                    'team_name' => $stat->team->team_name,
                ],
                'points' => $stat->points,
                'total_rebounds' => $stat->total_rebounds,
                'assists' => $stat->assists,
                'steals' => $stat->steals,
                'blocks_favor' => $stat->blocks_favor,
                'minutes' => $stat->minutes,
                'valuation' => $stat->valuation,
            ])
        ]);
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

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
            'filters' => [
                'search' => $request->search,
            ]
        ]);
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

        return Inertia::render('Teams/Show', [
            'team' => [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'team_code' => $team->team_code,
            ],
            'stats' => $stats,
            'recentGames' => $recentGames->map(fn ($stat) => [
                'id' => $stat->id,
                'game_id' => $stat->game->id,
                'game' => [
                    'game_code' => $stat->game->game_code,
                    'season_code' => $stat->game->season_code,
                ],
                'total_points' => $stat->total_points,
                'field_goals_made_2' => $stat->field_goals_made_2,
                'field_goals_attempted_2' => $stat->field_goals_attempted_2,
                'field_goals_made_3' => $stat->field_goals_made_3,
                'field_goals_attempted_3' => $stat->field_goals_attempted_3,
                'free_throws_made' => $stat->free_throws_made,
                'free_throws_attempted' => $stat->free_throws_attempted,
                'total_rebounds' => $stat->total_rebounds,
                'assists' => $stat->assists,
                'steals' => $stat->steals,
            ])
        ]);
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
            // Get next upcoming game for this team
            $nextGame = Game::where('season_code', 'E2025')
                ->where('is_played', false)
                ->where(function($query) use ($team) {
                    $query->where('home_team_code', $team->team_code)
                          ->orWhere('away_team_code', $team->team_code);
                })
                ->orderBy('game_date')
                ->first();

            if (!$nextGame) {
                continue; // Skip teams without upcoming games
            }

            // Get opponent team code
            $opponentCode = ($nextGame->home_team_code === $team->team_code)
                ? $nextGame->away_team_code
                : $nextGame->home_team_code;

            // Find opponent team
            $opponentTeam = Team::where('team_code', $opponentCode)->first();

            if (!$opponentTeam) {
                continue;
            }

            // Get games where opponent team played (their defensive record)
            $opponentGameIds = $opponentTeam->gameStats()->pluck('game_id');
            $totalGames = $opponentGameIds->count();

            // Get opponent's defensive stats vs this position
            // (how they allowed players at this position to perform)
            $defenseStats = PlayerGameStat::whereIn('game_id', $opponentGameIds)
                ->where('team_id', '!=', $opponentTeam->id) // Opposing players
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

            $totalPlayers = $defenseStats->total_players ?? 0;

            if ($totalGames > 0 && $totalPlayers > 0) {
                $teamStats[] = [
                    'team' => $team,
                    'next_opponent' => $opponentTeam,
                    'next_game' => $nextGame,
                    'stats' => (object)[
                        'games_count' => $totalGames,
                        'total_players' => $totalPlayers,
                        'avg_points' => $defenseStats->avg_points ?? 0,
                        'avg_rebounds' => $defenseStats->avg_rebounds ?? 0,
                        'avg_assists' => $defenseStats->avg_assists ?? 0,
                        'avg_pir' => $defenseStats->avg_pir ?? 0,
                        'avg_steals' => $defenseStats->avg_steals ?? 0,
                        'avg_blocks' => $defenseStats->avg_blocks ?? 0,
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

                // Get player's team and next opponent
                $playerTeam = Team::find($playerTeamId);
                $nextGame = null;
                $nextOpponent = null;
                $nextOpponentDefense = 0;

                if ($playerTeam) {
                    $nextGame = Game::where('season_code', 'E2025')
                        ->where('is_played', false)
                        ->where(function($query) use ($playerTeam) {
                            $query->where('home_team_code', $playerTeam->team_code)
                                  ->orWhere('away_team_code', $playerTeam->team_code);
                        })
                        ->orderBy('game_date')
                        ->first();

                    if ($nextGame) {
                        $nextOpponentCode = ($nextGame->home_team_code === $playerTeam->team_code)
                            ? $nextGame->away_team_code
                            : $nextGame->home_team_code;

                        $nextOpponent = Team::where('team_code', $nextOpponentCode)->first();
                    }
                }

                // Calculate average defense quality against this position across all opponents
                $positionCode = substr($playerPosition, 0, 1); // G, F, or C

                // Get next opponent's specific defense if available
                if ($nextOpponent) {
                    $opponentGameIds = $nextOpponent->gameStats()->pluck('game_id')->toArray();

                    if (!empty($opponentGameIds)) {
                        $opponentDefenseStats = PlayerGameStat::whereIn('game_id', $opponentGameIds)
                            ->where('team_id', '!=', $nextOpponent->id)
                            ->where('minutes', '>=', 18)
                            ->where('position', 'LIKE', "%{$positionCode}%")
                            ->selectRaw('AVG(valuation) as avg_pir')
                            ->first();

                        if ($opponentDefenseStats && $opponentDefenseStats->avg_pir) {
                            $nextOpponentDefense = $opponentDefenseStats->avg_pir;
                        }
                    }
                }

                // Get all teams' defense vs this position (for fallback)
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

                // Use next opponent's defense if available, otherwise use league average
                $avgOpponentDefense = $nextOpponentDefense > 0
                    ? $nextOpponentDefense
                    : (!empty($opponentDefenseScores) ? array_sum($opponentDefenseScores) / count($opponentDefenseScores) : 0);

                // Skip players without next opponent or defense data
                if (!$nextOpponent || $avgOpponentDefense == 0) {
                    continue;
                }

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
                    'team_name' => $playerTeam->team_name ?? 'Unknown',
                    'next_opponent' => $nextOpponent ? $nextOpponent->team_name : 'No game',
                    'next_opponent_code' => $nextOpponent ? $nextOpponent->team_code : '',
                    'next_game_date' => $nextGame ? $nextGame->game_date : null,
                    'is_home' => $nextGame && $nextGame->home_team_code === $playerTeam->team_code,
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

    public function syncData(Request $request)
    {
        try {
            $service = new EuroleagueStatsService();
            $results = [
                'schedule' => ['created' => 0, 'skipped' => 0, 'failed' => 0],
                'games' => ['success' => 0, 'failed' => 0, 'skipped' => 0],
                'positions' => ['updated' => 0, 'failed' => 0],
            ];

            // Step 1: Sync complete schedule (both played and future games)
            Log::info("Starting schedule sync...");
            $scheduleResults = $service->syncScheduleFromV2Api('E2025', false); // false = sync all games
            $results['schedule'] = $scheduleResults;

            // Step 2: Fetch stats for all played games that don't have stats yet
            Log::info("Fetching game stats...");
            $gamesToFetch = $service->getNextGamesToFetch('E2025', 50); // Get up to 50 games

            foreach ($gamesToFetch as $gameInfo) {
                $success = $service->fetchAndStoreGame($gameInfo['game_code'], 'E2025', true);

                if ($success) {
                    $results['games']['success']++;
                } else {
                    $results['games']['failed']++;
                }

                // Small delay to avoid overwhelming the API
                usleep(500000); // 0.5 seconds
            }

            // Step 3: Update player positions for players missing position data
            Log::info("Updating player positions...");
            $playersWithoutPosition = Player::whereNull('position')
                ->orWhere('position', '')
                ->limit(100)
                ->get();

            foreach ($playersWithoutPosition as $player) {
                // Try to get position from their game stats
                $latestStat = PlayerGameStat::where('player_id', $player->id)
                    ->whereNotNull('position')
                    ->orderBy('game_id', 'desc')
                    ->first();

                if ($latestStat && $latestStat->position) {
                    $player->update(['position' => $latestStat->position]);
                    $results['positions']['updated']++;
                } else {
                    $results['positions']['failed']++;
                }
            }

            Log::info("Sync completed", $results);

            return response()->json([
                'success' => true,
                'message' => 'Data sync completed successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error("Sync failed", ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
