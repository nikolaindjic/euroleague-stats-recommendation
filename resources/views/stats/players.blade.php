@extends('layouts.main')

@section('title', 'Players - Euroleague Stats')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">All Players</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Browse player statistics and performance</p>
</div>

<!-- Search Section -->
<div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
    <form method="GET" action="{{ route('stats.players') }}" class="flex flex-col sm:flex-row gap-4">
        <!-- Search Input -->
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Players</label>
            <input type="text"
                   name="search"
                   id="search"
                   value="{{ request('search') }}"
                   placeholder="Search by player name or jersey number..."
                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
        </div>

        <!-- Submit Button -->
        <div class="sm:w-32 flex items-end">
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
                Search
            </button>
        </div>

        <!-- Reset Button -->
        @if(request('search'))
        <div class="sm:w-32 flex items-end">
            <a href="{{ route('stats.players') }}"
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Player</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Games</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($players as $player)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $player->player_name }}
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        ID: {{ $player->player_id }}
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-center">
                    {{ $player->game_stats_count }}
                </td>
                <td class="px-6 py-4 text-sm text-center">
                    <a href="{{ route('stats.player', $player->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium transition-colors">
                        View Stats →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <p class="text-lg">No players found</p>
                    @if(request('search'))
                    <p class="text-sm mt-2">Try adjusting your search criteria</p>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $players->links() }}
</div>
@endsection

