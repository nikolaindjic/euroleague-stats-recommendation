@extends('layouts.main')

@section('title', 'Stats vs Position - Euroleague Stats')

@section('content')
<div class="mb-6">
    <h1 class="text-4xl font-bold text-gray-800 dark:text-white">Defense vs Position</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">How much each team allows to different positions</p>
</div>

<!-- Position Filter Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('stats.vs-position', ['position' => 'Guard']) }}"
               class="@if($positionFilter === 'Guard') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                🏀 Guards
            </a>
            <a href="{{ route('stats.vs-position', ['position' => 'Forward']) }}"
               class="@if($positionFilter === 'Forward') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                🏃 Forwards
            </a>
            <a href="{{ route('stats.vs-position', ['position' => 'Center']) }}"
               class="@if($positionFilter === 'Center') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                🏋️ Centers
            </a>
        </nav>
    </div>
</div>

<!-- Stats Summary Cards -->
@if(count($teamStats) > 0)
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @php
        $avgPoints = collect($teamStats)->avg('stats.avg_points');
        $avgRebounds = collect($teamStats)->avg('stats.avg_rebounds');
        $avgAssists = collect($teamStats)->avg('stats.avg_assists');
        $avgPir = collect($teamStats)->avg('stats.avg_pir');
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
        <div class="text-sm text-gray-600 dark:text-gray-400">League Avg PPG</div>
        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($avgPoints, 1) }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
        <div class="text-sm text-gray-600 dark:text-gray-400">League Avg RPG</div>
        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($avgRebounds, 1) }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
        <div class="text-sm text-gray-600 dark:text-gray-400">League Avg APG</div>
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($avgAssists, 1) }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
        <div class="text-sm text-gray-600 dark:text-gray-400">League Avg PIR</div>
        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($avgPir, 1) }}</div>
    </div>
</div>
@endif

<!-- Stats Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden transition-colors duration-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-900">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky left-16 bg-gray-50 dark:bg-gray-900">Team</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>PPG</div>
                        <div class="text-xs font-normal normal-case">Points Allowed</div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>RPG</div>
                        <div class="text-xs font-normal normal-case">Rebounds Allowed</div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>APG</div>
                        <div class="text-xs font-normal normal-case">Assists Allowed</div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>PIR</div>
                        <div class="text-xs font-normal normal-case">Performance Index</div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>SPG</div>
                        <div class="text-xs font-normal normal-case">Steals Per Game</div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>BPG</div>
                        <div class="text-xs font-normal normal-case">Blocks Per Game</div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div>Games</div>
                        <div class="text-xs font-normal normal-case">Sample Size</div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($teamStats as $index => $teamStat)
                @php
                    $rank = $index + 1;
                    $rankColor = '';
                    if ($rank <= 3) {
                        $rankColor = 'bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 dark:border-green-400';
                    } elseif ($rank >= count($teamStats) - 2) {
                        $rankColor = 'bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400';
                    }
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $rankColor }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-800">
                        <div class="flex items-center">
                            @if($rank === 1)
                                <span class="text-2xl">🥇</span>
                            @elseif($rank === 2)
                                <span class="text-2xl">🥈</span>
                            @elseif($rank === 3)
                                <span class="text-2xl">🥉</span>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">#{{ $rank }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap sticky left-16 bg-white dark:bg-gray-800">
                        <a href="{{ route('stats.team', $teamStat['team']->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors">
                            {{ $teamStat['team']->team_name }}
                        </a>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $teamStat['team']->team_code }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ number_format($teamStat['stats']->avg_points, 1) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @if($teamStat['stats']->avg_points < $avgPoints)
                                <span class="text-green-600 dark:text-green-400">↓ {{ number_format($avgPoints - $teamStat['stats']->avg_points, 1) }} below avg</span>
                            @else
                                <span class="text-red-600 dark:text-red-400">↑ {{ number_format($teamStat['stats']->avg_points - $avgPoints, 1) }} above avg</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        <div class="text-lg font-semibold">{{ number_format($teamStat['stats']->avg_rebounds, 1) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        <div class="text-lg font-semibold">{{ number_format($teamStat['stats']->avg_assists, 1) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        <div class="text-lg font-semibold">{{ number_format($teamStat['stats']->avg_pir, 1) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        <div class="text-sm">{{ number_format($teamStat['stats']->avg_steals, 1) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        <div class="text-sm">{{ number_format($teamStat['stats']->avg_blocks, 1) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                        <div class="text-sm">{{ $teamStat['stats']->total_performances }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <p class="text-lg">No data available for {{ $positionFilter }}s</p>
                        <p class="text-sm mt-2">Position data may need to be populated first</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Legend -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
    <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">📊 How to Read This Table</h3>
    <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
        <p>• <strong>Lower is better</strong> - Teams at the top allow fewer points to {{ $positionFilter }}s</p>
        <p>• <strong>Green highlight</strong> = Best defensive teams (Top 3)</p>
        <p>• <strong>Red highlight</strong> = Worst defensive teams (Bottom 3)</p>
        <p>• <strong>PPG</strong> shows average points allowed to opponent {{ $positionFilter }}s per game</p>
        <p>• <strong>PIR</strong> (Performance Index Rating) is the overall efficiency metric</p>
    </div>
</div>
@endsection

