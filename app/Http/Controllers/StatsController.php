<?php

namespace App\Http\Controllers;

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
        $positionFilter = $request->get('position', 'Guard');

        // Map position groups
        $positionMapping = [
            'Guard' => ['Guard', 'G'],
            'Forward' => ['Forward', 'F'],
            'Center' => ['Center', 'C'],
        ];

        $positions = $positionMapping[$positionFilter] ?? ['Guard', 'G'];

        // Get all teams
        $teams = Team::all();

        $teamStats = [];

        foreach ($teams as $team) {
            // Get stats allowed to opponents at this position
            // We need to find games where this team played and get opponent player stats
            $gameIds = $team->gameStats()->pluck('game_id');

            // Get opponent player stats (players from opponent teams in these games)
            $opponentStats = PlayerGameStat::whereIn('game_id', $gameIds)
                ->where('team_id', '!=', $team->id)
                ->where('is_playing', true)
                ->where(function($query) use ($positions) {
                    foreach ($positions as $index => $pos) {
                        if ($index === 0) {
                            $query->where('position', 'LIKE', "%{$pos}%");
                        } else {
                            $query->orWhere('position', 'LIKE', "%{$pos}%");
                        }
                    }
                })
                ->selectRaw('
                    COUNT(*) as total_performances,
                    AVG(points) as avg_points,
                    AVG(total_rebounds) as avg_rebounds,
                    AVG(assists) as avg_assists,
                    AVG(valuation) as avg_pir,
                    AVG(steals) as avg_steals,
                    AVG(blocks_favor) as avg_blocks
                ')
                ->first();

            if ($opponentStats && $opponentStats->total_performances > 0) {
                $teamStats[] = [
                    'team' => $team,
                    'stats' => $opponentStats,
                ];
            }
        }

        // Sort by avg_points allowed (descending - worst defense first)
        usort($teamStats, function($a, $b) {
            return $b['stats']->avg_points <=> $a['stats']->avg_points;
        });

        return view('stats.vs-position', compact('teamStats', 'positionFilter'));
    }
}

