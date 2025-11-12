@extends('layouts.main')

@section('title', 'Form & Matchup Recommendations - Euroleague Stats')

@section('content')
<div class="mb-6">
    <h1 class="text-4xl font-bold text-gray-800 dark:text-white">🎯 Form & Matchup Analyzer</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Find players in great form facing weak defenses</p>
    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">💡 Top-right quadrant = Best picks (good form + easy matchup)</p>
</div>

<!-- Filters -->
<div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
    <form method="GET" action="{{ route('stats.form-recommendations') }}" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label for="team" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Team</label>
            <select name="team" id="team" onchange="this.form.submit()"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                <option value="">All Teams</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ $selectedTeam == $team->id ? 'selected' : '' }}>
                        {{ $team->team_name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedTeam)
        <div>
            <a href="{{ route('stats.form-recommendations') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Clear Filter
            </a>
        </div>
        @endif
    </form>
</div>

<!-- Chart -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6 transition-colors duration-200">
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Performance Matrix</h2>
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing {{ count($playerData) }} players
        </div>
    </div>

    <div class="relative" style="height: 600px;">
        <canvas id="formChart"></canvas>
        <div id="chartLoading" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800">
            <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 dark:border-indigo-400 mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Loading chart...</p>
            </div>
        </div>
    </div>

    <!-- Chart Legend -->
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded border-l-4 border-green-500">
            <div class="font-bold text-green-700 dark:text-green-400">🌟 Top Right</div>
            <div class="text-gray-600 dark:text-gray-400">Best Picks: Great form + Easy matchup</div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded border-l-4 border-blue-500">
            <div class="font-bold text-blue-700 dark:text-blue-400">📈 Top Left</div>
            <div class="text-gray-600 dark:text-gray-400">Good form, tough matchup</div>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded border-l-4 border-yellow-500">
            <div class="font-bold text-yellow-700 dark:text-yellow-400">⚠️ Bottom Right</div>
            <div class="text-gray-600 dark:text-gray-400">Poor form, easy matchup</div>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded border-l-4 border-red-500">
            <div class="font-bold text-red-700 dark:text-red-400">❌ Bottom Left</div>
            <div class="text-gray-600 dark:text-gray-400">Avoid: Poor form + Tough matchup</div>
        </div>
    </div>
</div>

<!-- Top Recommendations Table -->
@php
    // Sort by combined score (form + opponent quality)
    $sortedPlayers = collect($playerData)->sortByDesc(function($p) {
        return $p['recent_form'] + $p['opponent_quality'];
    })->take(10);
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden transition-colors duration-200">
    <div class="px-6 py-4 bg-green-50 dark:bg-green-900/20 border-b border-green-200 dark:border-green-800">
        <h2 class="text-xl font-bold text-green-800 dark:text-green-300">🌟 Top 10 Recommendations</h2>
        <p class="text-sm text-green-600 dark:text-green-400 mt-1">Players with best combination of form and matchup</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Player</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Next Opponent</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Position</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Recent Form (PIR)</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Opponent Defense</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg Points</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg Rebounds</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg Assists</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($sortedPlayers as $index => $player)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($index === 0)
                            <span class="text-2xl">🥇</span>
                        @elseif($index === 1)
                            <span class="text-2xl">🥈</span>
                        @elseif($index === 2)
                            <span class="text-2xl">🥉</span>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">#{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('stats.player', $player['id']) }}"
                           class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors">
                            {{ $player['name'] }}
                        </a>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $player['team_name'] ?? 'Unknown' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $player['next_opponent'] ?? 'No game' }}
                        </div>
                        @if(isset($player['next_game_date']))
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ date('M j, H:i', strtotime($player['next_game_date'])) }}
                            <span class="ml-1 px-1.5 py-0.5 rounded text-xs {{ $player['is_home'] ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">
                                {{ $player['is_home'] ? 'H' : 'A' }}
                            </span>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if(str_contains($player['position'], 'G')) bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                            @elseif(str_contains($player['position'], 'F')) bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                            @else bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300
                            @endif">
                            {{ $player['position'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $player['recent_form'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Last {{ $player['games_played'] }} games</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-lg font-bold
                            @if($player['opponent_quality'] > 13) text-green-600 dark:text-green-400
                            @elseif($player['opponent_quality'] > 11) text-yellow-600 dark:text-yellow-400
                            @else text-red-600 dark:text-red-400
                            @endif">
                            {{ $player['opponent_quality'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @if($player['opponent_quality'] > 13) Easy
                            @elseif($player['opponent_quality'] > 11) Medium
                            @else Tough
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        {{ $player['recent_points'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        {{ $player['recent_rebounds'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
                        {{ $player['recent_assists'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Info Panel -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
    <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">📊 How to Read This Quadrant Chart</h3>
    <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
        <p>• <strong>Graph is centered at (0,0):</strong> Origin represents league average for both form and matchup quality</p>
        <p>• <strong>Values are standardized (z-scores):</strong> Each point shows how many standard deviations from average</p>
        <p>• <strong>X-Axis (Horizontal):</strong> Matchup quality relative to average (negative = tougher, positive = easier)</p>
        <p>• <strong>Y-Axis (Vertical):</strong> Player form relative to average (negative = worse, positive = better)</p>
        <p>• <strong>Distance from center = How unusual:</strong> Further from (0,0) = More extreme (very good or very bad)</p>
        <p>• <strong>Top-Right Quadrant (🌟):</strong> Above-average form + easier matchups = Best picks!</p>
        <p>• <strong>Top-Left Quadrant (📈):</strong> Good form but tough matchups = Risky but talented</p>
        <p>• <strong>Bottom-Right Quadrant (⚠️):</strong> Poor form but easy matchups = Bounce-back candidates</p>
        <p>• <strong>Bottom-Left Quadrant (❌):</strong> Poor form + tough matchups = Avoid!</p>
        <p>• <strong>Click any point</strong> to view full player details</p>
        <p>• <strong>Filter by team</strong> to analyze specific roster (results cached for 1 hour)</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load');
        document.getElementById('formChart').parentElement.innerHTML =
            '<div class="text-center p-8 text-red-600">Error: Chart library failed to load. Please refresh the page.</div>';
        return;
    }

    const playerData = @json($playerData);

    // Helper function to get surname from full name
    // Names are formatted as "SURNAME, Firstname" so we take the first part before comma
    function getSurname(fullName) {
        const parts = fullName.trim().split(',');
        // If there's a comma, take the part before it (surname)
        if (parts.length > 1) {
            return parts[0].trim();
        }
        // Otherwise, take the first word
        const words = fullName.trim().split(/\s+/);
        return words[0];
    }

    // Prepare data for Chart.js using normalized values (centered at 0)
    // X-axis = Opponent Quality (same for players of same position)
    // Y-axis = Recent Form (different for each player)
    const chartData = {
        datasets: [{
            label: 'Players',
            data: playerData.map(p => ({
                x: p.opponent_normalized,  // X-axis: Matchup Quality
                y: p.form_normalized,       // Y-axis: Player Form
                player: p,
                surname: getSurname(p.name) // Add surname for labeling
            })),
            backgroundColor: playerData.map(p => {
                // Color by position
                if (p.position.includes('G')) return 'rgba(59, 130, 246, 0.6)'; // Blue for Guards
                if (p.position.includes('F')) return 'rgba(34, 197, 94, 0.6)'; // Green for Forwards
                return 'rgba(168, 85, 247, 0.6)'; // Purple for Centers
            }),
            borderColor: playerData.map(p => {
                if (p.position.includes('G')) return 'rgba(59, 130, 246, 1)';
                if (p.position.includes('F')) return 'rgba(34, 197, 94, 1)';
                return 'rgba(168, 85, 247, 1)';
            }),
            borderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 10,
        }]
    };

    const ctx = document.getElementById('formChart');
    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }

    const isDark = document.documentElement.classList.contains('dark');

    try {
        const chart = new Chart(ctx.getContext('2d'), {
            type: 'scatter',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: isDark ? '#fff' : '#000',
                        bodyColor: isDark ? '#d1d5db' : '#4b5563',
                        borderColor: isDark ? '#4b5563' : '#d1d5db',
                        borderWidth: 1,
                        callbacks: {
                            title: function(context) {
                                return context[0].raw.player.name;
                            },
                            label: function(context) {
                                const p = context.raw.player;
                                const nextOpp = p.next_opponent || 'No game';
                                const gameDate = p.next_game_date ? new Date(p.next_game_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';
                                const venue = p.is_home ? '(H)' : '(A)';
                                return [
                                    `Team: ${p.team_name}`,
                                    `Next vs: ${nextOpp} ${gameDate} ${venue}`,
                                    `Position: ${p.position}`,
                                    `Form (PIR): ${p.recent_form}`,
                                    `Opponent Defense: ${p.opponent_quality}`,
                                    `Recent: ${p.recent_points} pts, ${p.recent_rebounds} reb, ${p.recent_assists} ast`
                                ];
                            }
                        }
                    },
                    datalabels: {
                        color: function(context) {
                            // Use darker color for text based on position
                            const p = context.dataset.data[context.dataIndex].player;
                            if (p.position.includes('G')) return isDark ? '#93c5fd' : '#1e40af'; // Blue
                            if (p.position.includes('F')) return isDark ? '#86efac' : '#15803d'; // Green
                            return isDark ? '#d8b4fe' : '#6b21a8'; // Purple
                        },
                        font: {
                            size: 10,
                            weight: 'bold',
                            family: 'Inter, sans-serif'
                        },
                        align: 'right',
                        anchor: 'end',
                        offset: 4,
                        formatter: function(value, context) {
                            return context.dataset.data[context.dataIndex].surname;
                        },
                        clip: false // Allow labels to overflow the chart area
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom',
                        min: -10,  // Force range to show negatives
                        max: 10,   // Force range to show positives
                        title: {
                            display: true,
                            text: '← Tougher Matchup | Easier Matchup →',
                            color: isDark ? '#d1d5db' : '#374151',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: true,
                            color: function(context) {
                                // Make the center line (0) more visible
                                return context.tick.value === 0
                                    ? (isDark ? '#9ca3af' : '#6b7280')
                                    : (isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(209, 213, 219, 0.5)');
                            },
                            lineWidth: function(context) {
                                return context.tick.value === 0 ? 2 : 1;
                            }
                        },
                        ticks: {
                            color: isDark ? '#d1d5db' : '#374151',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                if (value === 0) return '0 (Avg)';
                                return value > 0 ? '+' + value : value;
                            }
                        },
                        border: {
                            display: true,
                            color: isDark ? '#4b5563' : '#d1d5db',
                            width: 2
                        }
                    },
                    y: {
                        type: 'linear',
                        position: 'left',
                        min: -10,  // Force range to show negatives
                        max: 10,   // Force range to show positives
                        title: {
                            display: true,
                            text: '↑ Better Form | Worse Form ↓',
                            color: isDark ? '#d1d5db' : '#374151',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: true,
                            color: function(context) {
                                // Make the center line (0) more visible
                                return context.tick.value === 0
                                    ? (isDark ? '#9ca3af' : '#6b7280')
                                    : (isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(209, 213, 219, 0.5)');
                            },
                            lineWidth: function(context) {
                                return context.tick.value === 0 ? 2 : 1;
                            }
                        },
                        ticks: {
                            color: isDark ? '#d1d5db' : '#374151',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                if (value === 0) return '0 (Avg)';
                                return value > 0 ? '+' + value : value;
                            }
                        },
                        border: {
                            display: true,
                            color: isDark ? '#4b5563' : '#d1d5db',
                            width: 2
                        }
                    }
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const player = playerData[elements[0].index];
                        window.location.href = `/players/${player.id}`;
                    }
                }
            },
            plugins: [ChartDataLabels] // Register the datalabels plugin
        });

        // Hide loading indicator
        const loadingEl = document.getElementById('chartLoading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }

        console.log('Chart initialized successfully with', playerData.length, 'players');
    } catch (error) {
        console.error('Error initializing chart:', error);
        const loadingEl = document.getElementById('chartLoading');
        if (loadingEl) {
            loadingEl.innerHTML = '<div class="text-center p-8 text-red-600 dark:text-red-400">Error initializing chart: ' + error.message + '</div>';
        }
    }
});
</script>
@endpush
@endsection

