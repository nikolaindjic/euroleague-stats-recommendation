<template>
    <AppLayout>
        <Head title="Players - Euroleague Stats" />

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">All Players</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Browse player statistics and performance</p>
        </div>

        <!-- Search Section -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
            <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Players</label>
                    <input
                        v-model="searchQuery"
                        type="text"
                        id="search"
                        placeholder="Search by player name or jersey number..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                    />
                </div>

                <!-- Submit Button -->
                <div class="sm:w-32 flex items-end">
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded-md transition-colors"
                    >
                        Search
                    </button>
                </div>

                <!-- Reset Button -->
                <div v-if="searchQuery" class="sm:w-32 flex items-end">
                    <button
                        @click="resetSearch"
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Player</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Games</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="player in players.data" :key="player.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{ player.player_name }}
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                ID: {{ player.player_id }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-center">
                            {{ player.game_stats_count }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <Link :href="`/players/${player.id}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium transition-colors">
                                View Stats →
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="players.data.length === 0">
                        <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <p class="text-lg">No players found</p>
                            <p v-if="searchQuery" class="text-sm mt-2">Try adjusting your search criteria</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="players.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <Link
                        v-if="players.prev_page_url"
                        :href="players.prev_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="players.next_page_url"
                        :href="players.next_page_url"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Next
                    </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ players.from }}</span>
                            to
                            <span class="font-medium">{{ players.to }}</span>
                            of
                            <span class="font-medium">{{ players.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <component
                                v-for="(link, index) in players.links"
                                :key="index"
                                :is="link.url ? Link : 'span'"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    link.active ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === players.links.length - 1 ? 'rounded-r-md' : '',
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
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    players: Object,
    filters: Object
});

const searchQuery = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get('/players', { search: searchQuery.value }, {
        preserveState: true,
        replace: true
    });
};

const resetSearch = () => {
    searchQuery.value = '';
    router.get('/players', {}, {
        preserveState: true,
        replace: true
    });
};
</script>

