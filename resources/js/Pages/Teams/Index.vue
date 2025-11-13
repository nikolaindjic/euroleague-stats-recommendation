<template>
    <AppLayout>
        <Head title="Teams - Euroleague Stats" />

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">All Teams</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Browse team statistics and performance</p>
        </div>

        <!-- Search Section -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 transition-colors duration-200">
            <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Teams</label>
                    <input
                        v-model="searchQuery"
                        type="text"
                        id="search"
                        placeholder="Search by team name or code..."
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="team in teams.data" :key="team.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition-all duration-200">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">{{ team.team_name }}</h3>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ team.team_code }}</div>

                <div v-if="team.game_stats_count > 0" class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Games Played:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ team.game_stats_count }}</span>
                    </div>
                </div>

                <Link
                    :href="`/teams/${team.id}`"
                    class="block w-full text-center bg-indigo-600 dark:bg-indigo-500 text-white py-2 rounded hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors"
                >
                    View Details →
                </Link>
            </div>

            <div v-if="teams.data.length === 0" class="col-span-3 text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No teams found</p>
                <p v-if="searchQuery" class="text-sm mt-2 text-gray-400 dark:text-gray-500">Try adjusting your search criteria</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="teams.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <Link
                        v-if="teams.prev_page_url"
                        :href="teams.prev_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="teams.next_page_url"
                        :href="teams.next_page_url"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Next
                    </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ teams.from }}</span>
                            to
                            <span class="font-medium">{{ teams.to }}</span>
                            of
                            <span class="font-medium">{{ teams.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <component
                                v-for="(link, index) in teams.links"
                                :key="index"
                                :is="link.url ? Link : 'span'"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    link.active ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === teams.links.length - 1 ? 'rounded-r-md' : '',
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
    teams: Object,
    filters: Object
});

const searchQuery = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get('/teams', { search: searchQuery.value }, {
        preserveState: true,
        replace: true
    });
};

const resetSearch = () => {
    searchQuery.value = '';
    router.get('/teams', {}, {
        preserveState: true,
        replace: true
    });
};
</script>

