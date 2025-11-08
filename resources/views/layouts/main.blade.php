<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    </div>
                </div>

                <!-- Dark Mode Toggle -->
                <div class="flex items-center">
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
</body>
</html>
