<template>
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link href="/" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                Euroleague Stats
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                            <Link
                                href="/games"
                                :class="isActive(['games', 'games/*']) ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Games
                            </Link>
                            <Link
                                href="/teams"
                                :class="isActive(['teams', 'teams/*']) ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Teams
                            </Link>
                            <Link
                                href="/players"
                                :class="isActive(['players', 'players/*']) ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Players
                            </Link>
                            <Link
                                href="/stats-vs-position"
                                :class="isActive('stats-vs-position') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                Defense vs Position
                            </Link>
                            <Link
                                href="/form-recommendations"
                                :class="isActive('form-recommendations') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                            >
                                🎯 Recommendations
                            </Link>
                        </div>
                    </div>

                    <!-- Right side buttons -->
                    <div class="flex items-center space-x-3">
                        <!-- Sync Button -->
                        <SyncButton />

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="toggleDarkMode"
                            type="button"
                            class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5 transition-colors"
                        >
                            <svg v-if="!isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden px-4 pb-3 space-y-1">
                <Link
                    href="/games"
                    :class="isActive(['games', 'games/*']) ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                    class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                >
                    Games
                </Link>
                <Link
                    href="/teams"
                    :class="isActive(['teams', 'teams/*']) ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                    class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                >
                    Teams
                </Link>
                <Link
                    href="/players"
                    :class="isActive(['players', 'players/*']) ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                    class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                >
                    Players
                </Link>
                <Link
                    href="/stats-vs-position"
                    :class="isActive('stats-vs-position') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                    class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                >
                    Defense vs Position
                </Link>
                <Link
                    href="/form-recommendations"
                    :class="isActive('form-recommendations') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                    class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
                >
                    🎯 Recommendations
                </Link>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 w-full">
            <div class="px-4 sm:px-0">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto transition-colors duration-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                    © {{ new Date().getFullYear() }} Euroleague Stats. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SyncButton from '@/components/SyncButton.vue';

const isDark = ref(false);
const page = usePage();

onMounted(() => {
    // Check dark mode preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        isDark.value = true;
    } else {
        document.documentElement.classList.remove('dark');
        isDark.value = false;
    }
});

const toggleDarkMode = () => {
    isDark.value = !isDark.value;

    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const isActive = (routes) => {
    const currentPath = page.url.substring(1); // Remove leading slash

    if (Array.isArray(routes)) {
        return routes.some(route => {
            if (route.includes('*')) {
                const prefix = route.replace('/*', '');
                return currentPath.startsWith(prefix);
            }
            return currentPath === route;
        });
    }

    return currentPath === routes || currentPath.startsWith(routes + '/');
};
</script>

