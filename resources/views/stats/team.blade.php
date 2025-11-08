@extends('layouts.main')

@section('title', $team->team_name . ' - Euroleague Stats')

@section('content')
<div class="mb-6">
    <a href="{{ route('stats.teams') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-4 inline-block transition-colors">← Back to Teams</a>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $team->team_name }}</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Team Code: {{ $team->team_code }}</p>
</div>

<!-- Team Stats -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 transition-colors duration-200">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Team Statistics</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="text-center">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats->games_played ?? 0 }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Games Played</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($stats->avg_points ?? 0, 1) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">PPG</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($stats->avg_rebounds ?? 0, 1) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">RPG</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($stats->avg_assists ?? 0, 1) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">APG</div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="text-center">
            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats->avg_field_goals ?? 0, 1) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">FG Made Per Game</div>
        </div>
        <div class="text-center">
            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats->avg_steals ?? 0, 1) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">SPG</div>
        </div>
        <div class="text-center">
            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $stats->total_points ?? 0 }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Points</div>
        </div>
    </div>
</div>

<!-- Recent Games -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 transition-colors duration-200">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Recent Games</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Game</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PTS</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">FG2</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">FG3</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">FT</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">REB</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">AST</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">STL</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentGames as $gameStat)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('stats.game', $gameStat->game->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                            Game #{{ $gameStat->game->game_code }}
                        </a>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $gameStat->game->season_code }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-center font-bold text-gray-900 dark:text-white">{{ $gameStat->total_points }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $gameStat->field_goals_made_2 }}/{{ $gameStat->field_goals_attempted_2 }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $gameStat->field_goals_made_3 }}/{{ $gameStat->field_goals_attempted_3 }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $gameStat->free_throws_made }}/{{ $gameStat->free_throws_attempted }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $gameStat->total_rebounds }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $gameStat->assists }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $gameStat->steals }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No games found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

