import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb config: usa window.reverbConfig (injetado pelo Blade em produção)
// com fallback para VITE_ env vars (desenvolvimento local).
const config = window.reverbConfig || {};
const reverbKey = config.key || import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Pusher = Pusher;

    const isProduction = window.location.protocol === 'https:';
    const host = config.host || import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    const port = config.port || parseInt(import.meta.env.VITE_REVERB_PORT) || 8080;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: isProduction,
        disableStats: true,
        enabledTransports: isProduction ? ['wss'] : ['ws'],
    });

    console.log(`Echo: ${isProduction ? 'wss' : 'ws'}://${host}:${port}`);
} else {
    console.warn('Echo: REVERB_APP_KEY não configurado. WebSocket desativado.');
}
