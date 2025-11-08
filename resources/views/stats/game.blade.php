@extends('layouts.main')

@section('title', 'Game #' . $game->game_code . ' - Euroleague Stats')

@section('content')
<div class="mb-6">
    <a href="{{ route('stats.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-4 inline-block transition-colors">← Back to Games</a>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Game #{{ $game->game_code }}</h1>
    <div class="flex flex-wrap items-center gap-4 mt-2 text-gray-600 dark:text-gray-400">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
            Round {{ $game->round ?? ceil($game->game_code / 10) }}
        </span>
        <span>Season: {{ $game->season_code }}</span>
        @if($game->attendance)
        <span>• Attendance: {{ number_format($game->attendance) }}</span>
        @endif
        @if($game->referees)
        <span>• Referees: {{ $game->referees }}</span>
        @endif
    </div>
</div>

<!-- Score Summary -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    @foreach($teamStats as $teamId => $data)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 transition-colors duration-200">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">{{ $data['team']->team->team_name }}</h2>
        <div class="text-5xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">{{ $data['team']->total_points }}</div>

        <div class="grid grid-cols-4 gap-2 text-center text-sm mb-4">
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                <div class="font-semibold text-gray-900 dark:text-white">Q1</div>
                <div class="text-lg text-gray-900 dark:text-white">{{ $data['team']->quarter1 }}</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                <div class="font-semibold text-gray-900 dark:text-white">Q2</div>
                <div class="text-lg text-gray-900 dark:text-white">{{ $data['team']->quarter2 }}</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                <div class="font-semibold text-gray-900 dark:text-white">Q3</div>
                <div class="text-lg text-gray-900 dark:text-white">{{ $data['team']->quarter3 }}</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                <div class="font-semibold text-gray-900 dark:text-white">Q4</div>
                <div class="text-lg text-gray-900 dark:text-white">{{ $data['team']->quarter4 }}</div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-gray-600 dark:text-gray-400">FG 2PT</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->field_goals_made_2 }}/{{ $data['team']->field_goals_attempted_2 }}</div>
            </div>
            <div>
                <div class="text-gray-600 dark:text-gray-400">FG 3PT</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->field_goals_made_3 }}/{{ $data['team']->field_goals_attempted_3 }}</div>
            </div>
            <div>
                <div class="text-gray-600 dark:text-gray-400">FT</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->free_throws_made }}/{{ $data['team']->free_throws_attempted }}</div>
            </div>
            <div>
                <div class="text-gray-600 dark:text-gray-400">Rebounds</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->total_rebounds }}</div>
            </div>
            <div>
                <div class="text-gray-600 dark:text-gray-400">Assists</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->assists }}</div>
            </div>
            <div>
                <div class="text-gray-600 dark:text-gray-400">Steals</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->steals }}</div>
            </div>
        </div>

        @if($data['team']->coach)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <span class="text-gray-600 dark:text-gray-400">Coach:</span> <span class="font-semibold text-gray-900 dark:text-white">{{ $data['team']->coach }}</span>
        </div>
        @endif
    </div>
    @endforeach
</div>

<!-- Player Statistics -->
@foreach($teamStats as $teamId => $data)
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 transition-colors duration-200">
    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">{{ $data['team']->team->team_name }} - Player Stats</h3>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Player</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">MIN</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PTS</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">FG2</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">FG3</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">FT</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">REB</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">AST</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">STL</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">TO</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">+/-</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">VAL</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($data['players'] as $playerStat)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $playerStat->is_starter ? 'font-semibold' : '' }}">
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $playerStat->dorsal }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('stats.player', $playerStat->player->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                            {{ $playerStat->player->player_name }}
                        </a>
                        @if($playerStat->is_starter)
                        <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">(S)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->minutes ?? 'DNP' }}</td>
                    <td class="px-4 py-3 text-sm text-center font-bold text-gray-900 dark:text-white">{{ $playerStat->points }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->field_goals_made_2 }}/{{ $playerStat->field_goals_attempted_2 }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->field_goals_made_3 }}/{{ $playerStat->field_goals_attempted_3 }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->free_throws_made }}/{{ $playerStat->free_throws_attempted }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->total_rebounds }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->assists }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->steals }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->turnovers }}</td>
                    <td class="px-4 py-3 text-sm text-center font-medium {{ $playerStat->plus_minus > 0 ? 'text-green-600 dark:text-green-400' : ($playerStat->plus_minus < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white') }}">
                        {{ $playerStat->plus_minus > 0 ? '+' : '' }}{{ $playerStat->plus_minus }}
                    </td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ $playerStat->valuation }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection

