import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: { 
        host: '0.0.0.0', // Permite ligações externas
        hmr: {
            host: '192.168.1.111' // <--- SUBSTITUI PELO TEU IP (o mesmo do php artisan serve)
        },
    },
});