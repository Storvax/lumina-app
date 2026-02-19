import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import os from 'os';

// Função para descobrir o IP local automaticamente
function getLocalIP() {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            // Procura o IP que seja IPv4 e que não seja o localhost (127.0.0.1)
            if (iface.family === 'IPv4' && !iface.internal) {
                return iface.address;
            }
        }
    }
    return 'localhost'; // Fallback de segurança
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: { 
        host: '0.0.0.0', // Permite que o servidor Vite oiça ligações externas
        hmr: {
            host: getLocalIP(), // Injeta o IP dinâmico para o telemóvel encontrar o CSS
        },
    },
});