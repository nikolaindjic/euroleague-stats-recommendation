@extends('layouts.main')

@section('title', $player->player_name . ' - Euroleague Stats')

@section('content')
<div class="mb-6">
    <a href="{{ route('stats.players') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-4 inline-block transition-colors">
        ← Back to Players
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white">{{ $player->player_name }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Player ID: {{ $player->player_id }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <div class="inline-flex items-center px-4 py-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">{{ $stats->games_played ?? 0 }}</span>
                <span class="ml-2 text-sm text-indigo-800 dark:text-indigo-200">Games Played</span>
            </div>
        </div>
    </div>
</div>

<!-- Career Statistics -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Career Statistics</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Points Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                {{ number_format($stats->avg_points ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">PPG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                Total: {{ $stats->total_points ?? 0 }}
            </div>
        </div>

        <!-- Rebounds Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                {{ number_format($stats->avg_rebounds ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">RPG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Rebounds</div>
        </div>

        <!-- Assists Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                {{ number_format($stats->avg_assists ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">APG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Assists</div>
        </div>

        <!-- Steals Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                {{ number_format($stats->avg_steals ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">SPG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Steals</div>
        </div>

        <!-- Blocks Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                {{ number_format($stats->avg_blocks ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">BPG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Blocks</div>
        </div>

        <!-- Minutes Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">
                {{ number_format($stats->avg_minutes ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">MPG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Minutes</div>
        </div>

        <!-- Turnovers Per Game -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                {{ number_format($stats->avg_turnovers ?? 0, 1) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">TPG</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Turnovers</div>
        </div>

        <!-- Career High -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                {{ $stats->max_points ?? 0 }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Career High</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Points</div>
        </div>

        <!-- Total Points -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-indigo-600 dark:to-purple-700 rounded-lg shadow p-6 text-white">
            <div class="text-3xl font-bold">
                {{ $stats->total_points ?? 0 }}
            </div>
            <div class="text-sm mt-1 opacity-90">Total Points</div>
            <div class="text-xs mt-2 opacity-75">Career Total</div>
        </div>

        <!-- Games Played -->
        <div class="bg-gradient-to-br from-green-500 to-teal-600 dark:from-green-600 dark:to-teal-700 rounded-lg shadow p-6 text-white">
            <div class="text-3xl font-bold">
                {{ $stats->games_played ?? 0 }}
            </div>
            <div class="text-sm mt-1 opacity-90">Games</div>
            <div class="text-xs mt-2 opacity-75">Total Appearances</div>
        </div>
    </div>
</div>

<!-- Recent Games -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Recent Games (Last 10)</h2>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden transition-colors duration-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Game</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Team</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MIN</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PTS</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">REB</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">AST</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STL</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">BLK</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">TO</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">+/-</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PIR</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentGames as $gameStats)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('stats.game', $gameStats->game_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                                Game #{{ $gameStats->game->game_code }}
                            </a>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Round {{ $gameStats->game->round ?? ceil($gameStats->game->game_code / 10) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $gameStats->team->team_name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $gameStats->minutes ?? '0:00' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $gameStats->points >= 20 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $gameStats->points }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $gameStats->total_rebounds }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $gameStats->assists }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $gameStats->steals }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $gameStats->blocks_favor }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $gameStats->turnovers }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium {{ $gameStats->plus_minus > 0 ? 'text-green-600 dark:text-green-400' : ($gameStats->plus_minus < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white') }}">
                            {{ $gameStats->plus_minus > 0 ? '+' : '' }}{{ $gameStats->plus_minus }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900 dark:text-white">
                            {{ $gameStats->valuation }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <p class="text-lg">No game statistics available</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Shooting Statistics -->
@if($recentGames->isNotEmpty())
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Shooting Statistics</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 2-Point Shooting -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">2-Point Field Goals</h3>
            @php
                $fg2Made = $recentGames->sum('field_goals_made_2');
                $fg2Attempted = $recentGames->sum('field_goals_attempted_2');
                $fg2Percentage = $fg2Attempted > 0 ? ($fg2Made / $fg2Attempted * 100) : 0;
            @endphp
            <div class="flex items-end justify-between mb-2">
                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($fg2Percentage, 1) }}%
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $fg2Made }}/{{ $fg2Attempted }}
                </div>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full" style="width: {{ min($fg2Percentage, 100) }}%"></div>
            </div>
        </div>

        <!-- 3-Point Shooting -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">3-Point Field Goals</h3>
            @php
                $fg3Made = $recentGames->sum('field_goals_made_3');
                $fg3Attempted = $recentGames->sum('field_goals_attempted_3');
                $fg3Percentage = $fg3Attempted > 0 ? ($fg3Made / $fg3Attempted * 100) : 0;
            @endphp
            <div class="flex items-end justify-between mb-2">
                <div class="text-4xl font-bold text-purple-600 dark:text-purple-400">
                    {{ number_format($fg3Percentage, 1) }}%
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $fg3Made }}/{{ $fg3Attempted }}
                </div>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-purple-600 dark:bg-purple-400 h-2 rounded-full" style="width: {{ min($fg3Percentage, 100) }}%"></div>
            </div>
        </div>

        <!-- Free Throw Shooting -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Free Throws</h3>
            @php
                $ftMade = $recentGames->sum('free_throws_made');
                $ftAttempted = $recentGames->sum('free_throws_attempted');
                $ftPercentage = $ftAttempted > 0 ? ($ftMade / $ftAttempted * 100) : 0;
            @endphp
            <div class="flex items-end justify-between mb-2">
                <div class="text-4xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format($ftPercentage, 1) }}%
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $ftMade }}/{{ $ftAttempted }}
                </div>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-green-600 dark:bg-green-400 h-2 rounded-full" style="width: {{ min($ftPercentage, 100) }}%"></div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

