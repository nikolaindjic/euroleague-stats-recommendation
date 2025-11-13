<template>
    <AppLayout>
        <Head :title="`${player.player_name} - Euroleague Stats`" />

        <div class="mb-6">
            <Link href="/players" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-4 inline-block transition-colors">
                ← Back to Players
            </Link>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 dark:text-white">{{ player.player_name }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Player ID: {{ player.player_id }}</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <div class="inline-flex items-center px-4 py-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">{{ stats.games_played || 0 }}</span>
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
                        {{ (stats.avg_points || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">PPG</div>
                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                        Total: {{ stats.total_points || 0 }}
                    </div>
                </div>

                <!-- Rebounds Per Game -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ (stats.avg_rebounds || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">RPG</div>
                </div>

                <!-- Assists Per Game -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ (stats.avg_assists || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">APG</div>
                </div>

                <!-- Steals Per Game -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ (stats.avg_steals || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">SPG</div>
                </div>

                <!-- Blocks Per Game -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ (stats.avg_blocks || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">BPG</div>
                </div>

                <!-- Minutes Per Game -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">
                        {{ (stats.avg_minutes || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">MPG</div>
                </div>

                <!-- Turnovers Per Game -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                        {{ (stats.avg_turnovers || 0).toFixed(1) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">TPG</div>
                </div>

                <!-- Career High -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ stats.max_points || 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Career High</div>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Team</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">MIN</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PTS</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">REB</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">AST</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">STL</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PIR</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="game in recentGames" :key="game.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 text-sm">
                                <Link :href="`/games/${game.game_id}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                    Game #{{ game.game.game_code }}
                                </Link>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ game.game.season_code }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ game.team.team_name }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ game.minutes || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-gray-900 dark:text-white">{{ game.points }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ game.total_rebounds }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ game.assists }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ game.steals }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ game.valuation }}</td>
                        </tr>
                        <tr v-if="recentGames.length === 0">
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No games found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    player: Object,
    stats: Object,
    recentGames: Array
});
</script>

