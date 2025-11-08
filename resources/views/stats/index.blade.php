@extends('layouts.main')

@section('title', 'Games - Euroleague Stats')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">All Games</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Browse all Euroleague games and their results</p>
</div>

<!-- Search and Filter Section -->
<div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
    <form method="GET" action="{{ route('stats.index') }}" class="flex flex-col sm:flex-row gap-4">
        <!-- Search Input -->
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <input type="text"
                   name="search"
                   id="search"
                   value="{{ request('search') }}"
                   placeholder="Search by game code, season, or team..."
                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
        </div>

        <!-- Round Filter -->
        <div class="sm:w-48">
            <label for="round" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Round</label>
            <select name="round"
                    id="round"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                <option value="">All Rounds</option>
                @foreach($rounds as $round)
                    <option value="{{ $round }}" {{ request('round') == $round ? 'selected' : '' }}>
                        Round {{ $round }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Submit Button -->
        <div class="sm:w-32 flex items-end">
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
                Filter
            </button>
        </div>

        <!-- Reset Button -->
        @if(request('search') || request('round'))
        <div class="sm:w-32 flex items-end">
            <a href="{{ route('stats.index') }}"
               class="w-full text-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium py-2 px-4 rounded-md transition-colors">
                Reset
            </a>
        </div>
        @endif
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden transition-colors duration-200">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Game Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Round</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Season</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teams</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attendance</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($games as $game)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    #{{ $game->game_code }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                        Round {{ $game->round ?? ceil($game->game_code / 10) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ $game->season_code }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    @if($game->teamStats->count() >= 2)
                        <div class="space-y-1">
                            <div>{{ $game->teamStats[0]->team->team_name }}</div>
                            <div class="text-gray-500 dark:text-gray-400">vs</div>
                            <div>{{ $game->teamStats[1]->team->team_name }}</div>
                        </div>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">N/A</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    @if($game->teamStats->count() >= 2)
                        <div class="font-bold text-lg">
                            {{ $game->teamStats[0]->total_points }} - {{ $game->teamStats[1]->total_points }}
                        </div>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ $game->attendance ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a href="{{ route('stats.game', $game->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium transition-colors">
                        View Details →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <p class="text-lg">No games found</p>
                    @if(!request('search') && !request('round'))
                    <p class="text-sm mt-2">Run <code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">php artisan euroleague:fetch-stats</code> to fetch game data</p>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $games->links() }}
</div>
@endsection

