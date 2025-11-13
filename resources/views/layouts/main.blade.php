<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Euroleague Stats'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
    </style>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark Mode Script -->
    <script>
        // Check for saved theme preference or default to light mode
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col transition-colors duration-200">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('stats.index') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            Euroleague Stats
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                        <a href="{{ route('stats.index') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('stats.index') || request()->routeIs('stats.game') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white' }} text-sm font-medium transition-colors">
                            Games
                        </a>
                        <a href="{{ route('stats.teams') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('stats.teams') || request()->routeIs('stats.team') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white' }} text-sm font-medium transition-colors">
                            Teams
                        </a>
                        <a href="{{ route('stats.players') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('stats.players') || request()->routeIs('stats.player') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white' }} text-sm font-medium transition-colors">
                            Players
                        </a>
                        <a href="{{ route('stats.vs-position') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('stats.vs-position') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white' }} text-sm font-medium transition-colors">
                            Defense vs Position
                        </a>
                        <a href="{{ route('stats.form-recommendations') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('stats.form-recommendations') ? 'border-indigo-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-white' }} text-sm font-medium transition-colors">
                            🎯 Recommendations
                        </a>
                    </div>
                </div>

                <!-- Dark Mode Toggle -->
                <div class="flex items-center space-x-3">
                    <!-- Sync Button (Vue Component) -->
                    <div id="sync-button-app"></div>

                    <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5 transition-colors">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="sm:hidden px-4 pb-3 space-y-1">
            <a href="{{ route('stats.index') }}"
               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('stats.index') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                Games
            </a>
            <a href="{{ route('stats.teams') }}"
               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('stats.teams') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                Teams
            </a>
            <a href="{{ route('stats.players') }}"
               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('stats.players') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                Players
            </a>
            <a href="{{ route('stats.vs-position') }}"
               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('stats.vs-position') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                Defense vs Position
            </a>
            <a href="{{ route('stats.form-recommendations') }}"
               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('stats.form-recommendations') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                🎯 Recommendations
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 w-full">
        <div class="px-4 sm:px-0">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto transition-colors duration-200">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                © {{ date('Y') }} Euroleague Stats. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Dark Mode Toggle Script -->
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Show correct icon on page load
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        themeToggleBtn.addEventListener('click', function() {
            // Toggle icons
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // Toggle dark mode
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });
    </script>

    <!-- Sync Modal -->
    <div id="sync-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sync Game Data</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="sync-status" class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        This will sync:
                    </p>
                    <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                        <li>Complete game schedule (all games)</li>
                        <li>Game stats for played games</li>
                        <li>Player position data</li>
                    </ul>
                </div>
                <div id="sync-progress" class="hidden">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Syncing data... This may take a minute.
                    </p>
                </div>
                <div id="sync-results" class="hidden">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-400">Sync Complete!</h3>
                                <div id="sync-details" class="mt-2 text-sm text-green-700 dark:text-green-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="sync-error" class="hidden">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Sync Failed</h3>
                                <div id="error-message" class="mt-2 text-sm text-red-700 dark:text-red-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-6 space-x-3">
                    <button id="cancel-sync" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button id="confirm-sync" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 rounded-md transition-colors">
                        Start Sync
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const syncButton = document.getElementById('sync-button');
            const syncModal = document.getElementById('sync-modal');
            const closeModal = document.getElementById('close-modal');
            const cancelSync = document.getElementById('cancel-sync');
            const confirmSync = document.getElementById('confirm-sync');
            const syncStatus = document.getElementById('sync-status');
            const syncProgress = document.getElementById('sync-progress');
            const syncResults = document.getElementById('sync-results');
            const syncError = document.getElementById('sync-error');
            const syncIcon = document.getElementById('sync-icon');
            const syncText = document.getElementById('sync-text');

            console.log('Sync button:', syncButton);
            console.log('Sync modal:', syncModal);

            if (!syncButton || !syncModal) {
                console.error('Sync button or modal not found!');
                return;
            }

            // Open modal
            syncButton.addEventListener('click', function() {
                console.log('Sync button clicked!');
                syncModal.classList.remove('hidden');
                resetModal();
            });

            // Close modal
            closeModal.addEventListener('click', closeModalHandler);
            cancelSync.addEventListener('click', closeModalHandler);

            function closeModalHandler() {
                syncModal.classList.add('hidden');
            }

            function resetModal() {
                syncStatus.classList.remove('hidden');
                syncProgress.classList.add('hidden');
                syncResults.classList.add('hidden');
                syncError.classList.add('hidden');
                confirmSync.classList.remove('hidden');
                cancelSync.classList.remove('hidden');
            }

            // Start sync
            confirmSync.addEventListener('click', async function() {
                syncStatus.classList.add('hidden');
                syncProgress.classList.remove('hidden');
                confirmSync.classList.add('hidden');
                cancelSync.textContent = 'Please Wait...';
                cancelSync.disabled = true;

                // Add spinning animation to header button
                syncIcon.classList.add('animate-spin');

                try {
                    const response = await fetch('{{ route('stats.sync') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    syncProgress.classList.add('hidden');
                    syncIcon.classList.remove('animate-spin');

                    if (data.success) {
                        syncResults.classList.remove('hidden');

                        // Display results
                        const details = document.getElementById('sync-details');
                        details.innerHTML = `
                            <p><strong>Schedule:</strong> ${data.results.schedule.created} created, ${data.results.schedule.skipped} skipped</p>
                            <p><strong>Games:</strong> ${data.results.games.success} synced, ${data.results.games.failed} failed</p>
                            <p><strong>Positions:</strong> ${data.results.positions.updated} updated</p>
                        `;

                        cancelSync.textContent = 'Close';
                        cancelSync.disabled = false;

                        // Update button text temporarily
                        syncText.textContent = 'Synced!';
                        setTimeout(() => {
                            syncText.textContent = 'Sync Data';
                        }, 3000);
                    } else {
                        syncError.classList.remove('hidden');
                        document.getElementById('error-message').textContent = data.message;
                        cancelSync.textContent = 'Close';
                        cancelSync.disabled = false;
                    }
                } catch (error) {
                    syncProgress.classList.add('hidden');
                    syncError.classList.remove('hidden');
                    syncIcon.classList.remove('animate-spin');
                    document.getElementById('error-message').textContent = 'Network error: ' + error.message;
                    cancelSync.textContent = 'Close';
                    cancelSync.disabled = false;
                }
            });

            // Close modal when clicking outside
            syncModal.addEventListener('click', function(e) {
                if (e.target === syncModal) {
                    closeModalHandler();
                }
            });
        });
    </script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
