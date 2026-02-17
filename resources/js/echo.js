import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    
    // Usa o hostname atual (seja localhost ou 192.168.x.x)
    wsHost: window.location.hostname,
    
    wsPort: 8080,
    wssPort: 8080,
    
    // As tuas regras de ouro para local:
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws'], // Força WS
});

console.log('Echo a ligar a:', window.location.hostname + ':8080');