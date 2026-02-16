import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'tr4xr1bc07gjcha0ek1g', // Esta chave geralmente é pública, não faz mal estar no .env local
    wsHost: window.location.hostname, // <--- O TRUQUE: Usa o domínio do site atual (fly.dev)
    wsPort: 8080, // No Fly, o tráfego entra sempre por HTTPS (443)
    wssPort: 8080,
    forceTLS: true, // Força HTTPS/WSS
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
