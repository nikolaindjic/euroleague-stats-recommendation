<template>
    <AppLayout>
        <Head title="Games - Euroleague Stats" />

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">All Games</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Browse all Euroleague games and their results</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        id="search"
                        placeholder="Search by game code, season, or team..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                    />
                </div>

                <!-- Round Filter -->
                <div class="sm:w-48">
                    <label for="round" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Round</label>
                    <select
                        v-model="filters.round"
                        id="round"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                    >
                        <option value="">All Rounds</option>
                        <option v-for="round in rounds" :key="round" :value="round">
                            Round {{ round }}
                        </option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="sm:w-32 flex items-end">
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded-md transition-colors"
                    >
                        Filter
                    </button>
                </div>

                <!-- Reset Button -->
                <div v-if="hasFilters" class="sm:w-32 flex items-end">
                    <button
                        @click="resetFilters"
                        type="button"
                        class="w-full text-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium py-2 px-4 rounded-md transition-colors"
                    >
                        Reset
                    </button>
                </div>
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
                    <tr
                        v-for="game in games.data"
                        :key="game.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            #{{ game.game_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                Round {{ game.round || Math.ceil(game.game_code / 10) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ game.season_code }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <div v-if="game.team_stats && game.team_stats.length >= 2" class="space-y-1">
                                <div>{{ game.team_stats[0].team.team_name }}</div>
                                <div class="text-gray-500 dark:text-gray-400">vs</div>
                                <div>{{ game.team_stats[1].team.team_name }}</div>
                            </div>
                            <span v-else class="text-gray-400 dark:text-gray-500">N/A</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <div v-if="game.team_stats && game.team_stats.length >= 2" class="font-bold text-lg">
                                {{ game.team_stats[0].total_points }} - {{ game.team_stats[1].total_points }}
                            </div>
                            <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ game.attendance ? game.attendance.toLocaleString() : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <Link
                                :href="`/games/${game.id}`"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                            >
                                View Details
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="games.data.length === 0">
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No games found matching your criteria.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="games.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <Link
                        v-if="games.prev_page_url"
                        :href="games.prev_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="games.next_page_url"
                        :href="games.next_page_url"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Next
                    </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ games.from }}</span>
                            to
                            <span class="font-medium">{{ games.to }}</span>
                            of
                            <span class="font-medium">{{ games.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <component
                                v-for="(link, index) in games.links"
                                :key="index"
                                :is="link.url ? Link : 'span'"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    link.active ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === games.links.length - 1 ? 'rounded-r-md' : '',
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                ]"
                            />
                        </nav>
                    </div>
                </div>
            </nav>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    games: Object,
    rounds: Array,
    filters: Object
});

const filters = ref({
    search: props.filters?.search || '',
    round: props.filters?.round || ''
});

const hasFilters = computed(() => {
    return filters.value.search || filters.value.round;
});

const handleFilter = () => {
    router.get('/games', filters.value, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        round: ''
    };
    router.get('/games', {}, {
        preserveState: true,
        replace: true
    });
};
</script>

