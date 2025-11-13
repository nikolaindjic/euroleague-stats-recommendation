import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
                'app/Http/Controllers/**/*.php',
            ],
            buildDirectory: 'build',
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
        // Fix for Laravel expecting manifest.json in build root
        // Vite 7+ puts it in .vite/manifest.json by default
        ssrManifest: false,
    },
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
