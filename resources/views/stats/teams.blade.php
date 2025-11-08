@extends('layouts.main')

@section('title', 'Teams - Euroleague Stats')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">All Teams</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Browse team statistics and performance</p>
</div>

<!-- Search Section -->
<div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
    <form method="GET" action="{{ route('stats.teams') }}" class="flex flex-col sm:flex-row gap-4">
        <!-- Search Input -->
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Teams</label>
            <input type="text"
                   name="search"
                   id="search"
                   value="{{ request('search') }}"
                   placeholder="Search by team name or code..."
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
            <a href="{{ route('stats.teams') }}"
               class="w-full text-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium py-2 px-4 rounded-md transition-colors">
                Reset
            </a>
        </div>
        @endif
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($teams as $team)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition-all duration-200">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">{{ $team->team_name }}</h3>
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $team->team_code }}</div>

        @if($team->gameStats->isNotEmpty())
        <div class="space-y-2 text-sm mb-4">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Games Played:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $team->game_stats_count }}</span>
            </div>
        </div>
        @endif

        <a href="{{ route('stats.team', $team->id) }}" class="block w-full text-center bg-indigo-600 dark:bg-indigo-500 text-white py-2 rounded hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors">
            View Details →
        </a>
    </div>
    @empty
    <div class="col-span-3 text-center py-12">
        <p class="text-gray-500 dark:text-gray-400 text-lg">No teams found</p>
        @if(request('search'))
        <p class="text-sm mt-2 text-gray-400 dark:text-gray-500">Try adjusting your search criteria</p>
        @endif
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $teams->links() }}
</div>
@endsection

