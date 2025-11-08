import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
                'app/Http/Controllers/**/*.php',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
        host: 'localhost',
        port: 5173,
        strictPort: true,
        watch: {
            usePolling: true,
        },
    },
});
