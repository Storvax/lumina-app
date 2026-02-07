import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0', // Permite que o servidor seja acedido externamente
        cors: true,      // <--- A CHAVE MÁGICA: Permite pedidos de qualquer origem
        hmr: {
            host: '192.168.1.111', // O IP do teu PC (onde o telemóvel vai bater)
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});