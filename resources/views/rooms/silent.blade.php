<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Sala de Silêncio | Lumina</title>
    <meta name="theme-color" content="#020617">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .particle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(251,191,36,1) 0%, rgba(245,158,11,0.4) 50%, transparent 100%);
            animation: float 10s infinite ease-in-out alternate, pulse 4s infinite;
            filter: blur(2px);
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.2); }
            100% { transform: translate(-15px, 20px) scale(0.9); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }

        /* O teu pulso pessoal (Ripple) */
        .my-pulse {
            animation: ripple 2s cubic-bezier(0, 0.2, 0.8, 1) forwards;
        }
        @keyframes ripple {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(8); opacity: 0; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans overflow-hidden" x-data="silentRoom()">

    {{-- Navegação superior discreta --}}
    <div class="relative z-50 p-6 flex justify-between items-center opacity-30 hover:opacity-100 transition-opacity duration-500">
        <a href="{{ route('rooms.index') }}" class="text-slate-400 hover:text-white flex items-center gap-2 font-bold transition-colors">
            <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm">Voltar às Salas</span>
        </a>
        <div class="flex items-center gap-2 text-amber-500/80 font-medium text-xs bg-slate-900/50 px-3 py-1.5 rounded-full border border-slate-800">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            <span x-text="usersCount">1</span> <span x-text="usersCount === 1 ? 'pessoa aqui' : 'pessoas aqui'"></span>
        </div>
    </div>

    {{-- Área Principal: Onde a magia da presença acontece --}}
    <main class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none">
        
        {{-- Mensagem Central --}}
        <div class="text-center z-20 pointer-events-auto transition-opacity duration-1000" :class="showIntro ? 'opacity-100' : 'opacity-0'">
            <h1 class="text-xl md:text-2xl font-medium text-slate-300 mb-2 font-serif italic tracking-wide">
                Não precisas de dizer nada.
            </h1>
            <p class="text-slate-500 text-xs md:text-sm">
                Apenas respira. Cada luz em teu redor é alguém que também está acordado.
            </p>
        </div>

        {{-- O Fogo Central / Ponto de Foco --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40vw] h-[40vw] bg-amber-600/5 rounded-full blur-[80px] pointer-events-none"></div>

        {{-- Contentor das Faíscas (Outros Utilizadores) --}}
        <div id="particles-container" class="absolute inset-0 pointer-events-none">
            <template x-for="user in otherUsers" :key="user.id">
                <div class="particle"
                     :style="`top: ${user.y}%; left: ${user.x}%; width: ${user.size}px; height: ${user.size}px; animation-delay: ${user.delay}s;`">
                </div>
            </template>
        </div>

        {{-- Efeito do Próprio Utilizador (Ripple) --}}
        <div id="my-ripple-container" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
    </main>

    {{-- Botão de Interação Silenciosa --}}
    <div class="relative z-50 mt-auto p-8 flex justify-center pb-12 opacity-30 hover:opacity-100 transition-opacity duration-500">
        <button @click="sendPulse()" 
                class="group relative w-16 h-16 rounded-full bg-slate-900 border-2 border-slate-800 text-slate-400 hover:border-amber-500/50 hover:text-amber-400 hover:bg-slate-800 transition-all flex items-center justify-center focus:outline-none"
                title="Emitir um pulso de presença">
            <i class="ri-signal-wifi-1-line text-2xl group-hover:animate-ping"></i>
        </button>
    </div>

    <script>
        function silentRoom() {
            return {
                showIntro: true,
                usersCount: 1, // Tu contas sempre como 1
                otherUsers: [],
                channel: null,

                init() {
                    // Esconde a intro após 8 segundos para total imersão
                    setTimeout(() => { this.showIntro = false; }, 8000);

                    if (window.Echo) {
                        // Junta-se ao Presence Channel "silent-room"
                        this.channel = window.Echo.join('silent-room')
                            .here((users) => {
                                this.usersCount = users.length;
                                this.generateParticles(users);
                            })
                            .joining((user) => {
                                this.usersCount++;
                                this.addParticle(user);
                            })
                            .leaving((user) => {
                                this.usersCount--;
                                this.removeParticle(user);
                            })
                            .listenForWhisper('pulse', (e) => {
                                this.showRemotePulse(e.userId);
                            });
                    }
                },

                // Cria as luzes no ecrã para quem já lá está
                generateParticles(users) {
                    const currentUserId = {{ Auth::id() ?? 0 }};
                    users.forEach(user => {
                        if (user.id !== currentUserId) {
                            this.addParticle(user);
                        }
                    });
                },

                addParticle(user) {
                    // Posição aleatória no ecrã
                    const x = Math.floor(Math.random() * 80) + 10; // 10% to 90%
                    const y = Math.floor(Math.random() * 80) + 10;
                    const size = Math.floor(Math.random() * 15) + 10; // 10px to 25px
                    const delay = (Math.random() * 5).toFixed(2);
                    
                    this.otherUsers.push({ id: user.id, x, y, size, delay });
                },

                removeParticle(user) {
                    this.otherUsers = this.otherUsers.filter(u => u.id !== user.id);
                },

                // Envia um "Sinal de Vida" silencioso aos outros
                sendPulse() {
                    // Feedback visual para ti mesmo
                    const container = document.getElementById('my-ripple-container');
                    const ripple = document.createElement('div');
                    ripple.className = 'w-20 h-20 bg-amber-500/30 rounded-full my-pulse absolute -top-10 -left-10';
                    container.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 2000);

                    // Vibração se suportado
                    if (window.navigator.vibrate) window.navigator.vibrate(40);

                    // Envia via WebSocket usando Whisper (não precisa de gravar na BD)
                    if (this.channel) {
                        this.channel.whisper('pulse', { userId: {{ Auth::id() ?? 0 }} });
                    }
                },

                // Quando alguém envia um pulso
                showRemotePulse(userId) {
                    // Ilumina momentaneamente o ecrã inteiro
                    document.body.style.backgroundColor = '#1e1b4b'; // slate-950 mais claro
                    setTimeout(() => { document.body.style.backgroundColor = '#020617'; }, 500);
                }
            }
        }
    </script>
</body>
</html>