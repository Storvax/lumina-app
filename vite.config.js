import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import os from 'os';

function getLanAddress() {
    for (const nets of Object.values(os.networkInterfaces())) {
        for (const net of nets) {
            if (net.family === 'IPv4' && !net.internal) {
                return net.address;
            }
        }
    }
    return 'localhost';
}

const host = getLanAddress();

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        cors: true,
        origin: `http://${host}:5173`,
    },
});
