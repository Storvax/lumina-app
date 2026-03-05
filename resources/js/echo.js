import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb config: usa window.reverbConfig (injetado pelo Blade em produção)
// com fallback para VITE_ env vars (desenvolvimento local).
const config = window.reverbConfig || {};
const reverbKey = config.key || import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Pusher = Pusher;

    // 🚀 Ativar logs do Pusher para vermos exatamente o que ele faz na consola (F12)
    Pusher.logToConsole = true;

    const isProduction = window.location.protocol === 'https:';
    const host = config.host || import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    
    // Em desenvolvimento local pode ser 8080, em produção o Railway exige 80 e 443
    const localPort = config.port || parseInt(import.meta.env.VITE_REVERB_PORT) || 8080;

    // CSRF token para autenticação de canais privados/presença (/broadcasting/auth)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: host,
        wsPort: isProduction ? 80 : localPort,
        wssPort: isProduction ? 443 : localPort,
        forceTLS: isProduction,
        encrypted: isProduction,
        disableStats: true,
        // ⚠️ O nome do transporte é SEMPRE 'ws' (a flag forceTLS é que o torna seguro)
        enabledTransports: ['ws', 'wss'], 
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        },
    });

    // 📡 Listener global para detetar as mudanças de estado do WebSocket em tempo real
    window.Echo.connector.pusher.connection.bind('state_change', function(states) {
        console.log(`[Echo Status]: Mudou de '${states.previous}' para '${states.current}'`);
    });

    console.log(`Echo configurado para apontar a: ${isProduction ? 'wss' : 'ws'}://${host}`);
} else {
    console.warn('Echo: REVERB_APP_KEY não configurado. WebSocket desativado.');
}
