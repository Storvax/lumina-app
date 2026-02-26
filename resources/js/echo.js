import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Pusher = Pusher;

    // Detecta se está em HTTPS (produção) ou HTTP (local)
    const isProduction = window.location.protocol === 'https:';
    const port = isProduction ? 443 : 8080;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: window.location.hostname,
        wsPort: port,
        wssPort: port,
        forceTLS: isProduction,
        disableStats: true,
        enabledTransports: isProduction ? ['wss'] : ['ws'],
    });

    console.log(`Echo: ${isProduction ? 'wss' : 'ws'}://${window.location.hostname}:${port}`);
}
