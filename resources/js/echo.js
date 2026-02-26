import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Pusher = Pusher;

    // Detecta se está em HTTPS (produção) ou HTTP (local)
    const isProduction = window.location.protocol === 'https:';
    const wsScheme = isProduction ? 'wss' : 'ws';
    const port = isProduction ? 443 : 8080;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: window.location.hostname,
        wsPort: port,
        wssPort: port,

        // Em produção (HTTPS), força TLS (WSS)
        // Em desenvolvimento (HTTP), desativa TLS (WS)
        forceTLS: isProduction,
        disableStats: true,

        // Em produção, usa WSS; em dev, usa WS
        enabledTransports: isProduction ? ['wss'] : ['ws'],
    });

    console.log(`Echo conectando a ${wsScheme}://${window.location.hostname}:${port}`);
}
