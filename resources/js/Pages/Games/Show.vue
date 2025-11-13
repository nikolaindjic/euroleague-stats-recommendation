<template>
    <AppLayout>
        <Head :title="`Game #${game.game_code} - Euroleague Stats`" />

        <div class="mb-6">
            <Link href="/games" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-4 inline-block transition-colors">
                ← Back to Games
            </Link>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Game #{{ game.game_code }}</h1>
            <div class="flex flex-wrap items-center gap-4 mt-2 text-gray-600 dark:text-gray-400">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                    Round {{ game.round || Math.ceil(game.game_code / 10) }}
                </span>
                <span>Season: {{ game.season_code }}</span>
                <span v-if="game.attendance">• Attendance: {{ game.attendance.toLocaleString() }}</span>
                <span v-if="game.referees">• Referees: {{ game.referees }}</span>
            </div>
        </div>

        <!-- Score Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div v-for="teamData in teamStats" :key="teamData.team.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 transition-colors duration-200">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">{{ teamData.team.team_name }}</h2>
                <div class="text-5xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">{{ teamData.team_stat.total_points }}</div>

                <div class="grid grid-cols-4 gap-2 text-center text-sm mb-4">
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                        <div class="font-semibold text-gray-900 dark:text-white">Q1</div>
                        <div class="text-lg text-gray-900 dark:text-white">{{ teamData.team_stat.quarter1 }}</div>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                        <div class="font-semibold text-gray-900 dark:text-white">Q2</div>
                        <div class="text-lg text-gray-900 dark:text-white">{{ teamData.team_stat.quarter2 }}</div>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                        <div class="font-semibold text-gray-900 dark:text-white">Q3</div>
                        <div class="text-lg text-gray-900 dark:text-white">{{ teamData.team_stat.quarter3 }}</div>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded transition-colors">
                        <div class="font-semibold text-gray-900 dark:text-white">Q4</div>
                        <div class="text-lg text-gray-900 dark:text-white">{{ teamData.team_stat.quarter4 }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">FG 2PT</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.field_goals_made_2 }}/{{ teamData.team_stat.field_goals_attempted_2 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">FG 3PT</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.field_goals_made_3 }}/{{ teamData.team_stat.field_goals_attempted_3 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">FT</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.free_throws_made }}/{{ teamData.team_stat.free_throws_attempted }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Rebounds</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.total_rebounds }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Assists</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.assists }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Steals</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.steals }}</div>
                    </div>
                </div>

                <div v-if="teamData.team_stat.coach" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Coach:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ teamData.team_stat.coach }}</span>
                </div>
            </div>
        </div>

        <!-- Player Statistics -->
        <div v-for="teamData in teamStats" :key="`players-${teamData.team.id}`" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 transition-colors duration-200">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">{{ teamData.team.team_name }} - Player Stats</h3>

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
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PIR</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="player in teamData.players" :key="player.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ player.dorsal || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium">
                                <Link :href="`/players/${player.player_id}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    {{ player.player_name }}
                                </Link>
                                <div v-if="player.is_starter" class="text-xs text-gray-500 dark:text-gray-400">Starter</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.minutes || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-gray-900 dark:text-white">{{ player.points }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.field_goals_made_2 }}/{{ player.field_goals_attempted_2 }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.field_goals_made_3 }}/{{ player.field_goals_attempted_3 }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.free_throws_made }}/{{ player.free_throws_attempted }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.total_rebounds }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.assists }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">{{ player.valuation }}</td>
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

const props = defineProps({
    game: Object,
    teamStats: Array
});
</script>

