import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY, // A chave geralmente lê bem
    wsHost: '192.168.1.111',  // <--- ESCREVE O TEU IP DIRETAMENTE AQUI
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws'], // Força WS (sem Secure)
});

// Log para veres no browser se ele está a tentar ligar
console.log('Echo a tentar ligar a: 192.168.1.111:8080');