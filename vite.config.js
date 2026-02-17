import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: { // Adiciona isto
        hmr: {
            host: 'localhost',
            protocol: 'ws', // Força o Hot Module Replacement a usar ws
        },
    },
});