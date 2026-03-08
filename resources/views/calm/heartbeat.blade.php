<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Sintonia | Lumina</title>
    <meta name="theme-color" content="#4c0519">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-rose-950 text-rose-100 min-h-screen flex flex-col font-sans selection:bg-rose-500/30 selection:text-rose-200"
      :class="(state === 'active' || state === 'countdown') ? 'overflow-hidden' : ''"
      x-data="communityHeartbeat()">

    {{-- Fundo Dinâmico --}}
    <div class="absolute inset-0 bg-gradient-to-b from-rose-900/20 to-rose-950 -z-20"></div>
    
    {{-- Navegação Segura (Oculta durante a pulsação para evitar misclicks) --}}
    <div class="relative z-20 p-6 flex justify-between items-center transition-opacity duration-500" 
         :class="state !== 'idle' ? 'opacity-0 pointer-events-none' : 'opacity-100'">
        <a href="{{ route('calm.index') }}" class="text-rose-500 hover:text-white flex items-center gap-2 font-bold transition-colors min-h-[44px]">
            <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm uppercase tracking-widest">Zona Calma</span>
        </a>
    </div>

    {{-- OVERLAY DE PREPARAÇÃO (COUNTDOWN) --}}
    <div x-show="state === 'countdown'" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-rose-950/95 backdrop-blur-md" x-cloak>
         <i class="ri-smartphone-line text-5xl text-rose-400 mb-6 animate-pulse"></i>
         <h2 class="text-2xl font-black text-white text-center px-6 mb-8 leading-tight">Encosta o telemóvel ao teu peito...</h2>
         <span class="text-7xl font-bold text-rose-500" x-text="counter"></span>
    </div>

    <main class="flex-1 flex flex-col items-center justify-center relative z-10 px-6 py-12">
        
        {{-- Texto Principal (Oculto em modo ativo) --}}
        <div class="text-center mb-16 transition-all duration-700" :class="state === 'active' ? 'opacity-0 translate-y-[-20px] pointer-events-none' : 'opacity-100'">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold uppercase tracking-widest mb-6">
                <i class="ri-heart-pulse-fill"></i> Sincronia Somática
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-white mb-4 tracking-tight">Um coração emprestado.</h1>
            <p class="text-rose-200/70 text-base max-w-md mx-auto leading-relaxed">
                Usa a vibração do teu telemóvel para simular um batimento cardíaco em repouso (60 bpm). Sincroniza a tua respiração com o ritmo.
            </p>
        </div>

        {{-- O Coração Visual --}}
        <div class="relative w-48 h-48 flex items-center justify-center">
            {{-- Ondas de pulso --}}
            <div class="absolute inset-0 bg-rose-500/10 rounded-full transition-transform duration-300 ease-out"
                 :class="isBeating ? 'scale-150 opacity-100' : 'scale-100 opacity-50'"></div>
            <div class="absolute inset-0 bg-rose-500/20 rounded-full transition-transform duration-500 ease-out delay-75"
                 :class="isBeating ? 'scale-[1.8] opacity-0' : 'scale-100 opacity-80'"></div>
            
            {{-- Coração Físico --}}
            <div class="relative z-10 w-32 h-32 rounded-full bg-gradient-to-tr from-rose-700 to-rose-500 flex items-center justify-center shadow-[0_0_50px_rgba(225,29,72,0.4)] transition-transform duration-100"
                 :class="isBeating ? 'scale-110 brightness-125' : 'scale-100'">
                <i class="ri-heart-pulse-fill text-5xl text-white opacity-90"></i>
            </div>
        </div>

        {{-- Controlo (Iniciar) --}}
        <div class="mt-20 relative z-20 transition-all duration-500" x-show="state === 'idle'">
            <button @click="prepare()" class="group relative px-8 py-4 bg-rose-600 hover:bg-rose-500 text-white rounded-full font-bold text-lg shadow-xl shadow-rose-600/30 transition-all active:scale-95 overflow-hidden flex items-center gap-3 min-h-[56px]">
                <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:animate-[shimmer_1.5s_infinite]"></div>
                <i class="ri-play-fill text-xl"></i>
                <span>Sintonizar</span>
            </button>
        </div>

    </main>

    {{-- Controlo (Terminar) - Fixo no Fundo para Toque Intencional --}}
    <div class="fixed bottom-10 left-0 w-full flex justify-center z-40 transition-all duration-700"
         x-show="state === 'active'" x-transition:enter="transition ease-out duration-700 delay-300"
         x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <button @click="stop()" class="px-8 py-3 bg-rose-950/80 border border-rose-500/30 text-rose-300 rounded-full font-bold text-sm shadow-xl hover:bg-rose-900 transition-colors backdrop-blur-md min-h-[44px]">
            Terminar Sintonia
        </button>
    </div>

    <script>
        function communityHeartbeat() {
            return {
                state: 'idle', // 'idle', 'countdown', 'active'
                counter: 3,
                isBeating: false,
                heartbeatInterval: null,

                prepare() {
                    if (!window.navigator || !window.navigator.vibrate) {
                        alert('O teu dispositivo não suporta feedback háptico (vibração) no browser ou precisas de desativar o modo de poupança de bateria.');
                        return;
                    }

                    this.state = 'countdown';
                    this.counter = 3;

                    // Contagem regressiva
                    let timer = setInterval(() => {
                        this.counter--;
                        if (this.counter <= 0) {
                            clearInterval(timer);
                            this.startBeating();
                        }
                    }, 1000);
                },

                startBeating() {
                    this.state = 'active';
                    
                    // Ritmo Cardíaco de Repouso (Resting Heart Rate) - aprox 60 BPM
                    // Padrão: Lub (forte), pausa curta, Dub (mais fraco), pausa longa
                    this.beat(); // Dá o primeiro batimento imediatamente
                    this.heartbeatInterval = setInterval(() => {
                        this.beat();
                    }, 1000); // 1000ms = 60 batimentos por minuto
                },

                beat() {
                    // Feedback visual e háptico sincronizados
                    this.isBeating = true;
                    // Tenta forçar a vibração no motor háptico
                    if (window.navigator.vibrate) {
                        window.navigator.vibrate([80, 150, 40]); // Vibra 80ms, pausa 150ms, vibra 40ms
                    }
                    
                    setTimeout(() => {
                        this.isBeating = false;
                    }, 300); // O visual volta ao normal
                },

                stop() {
                    this.state = 'idle';
                    this.isBeating = false;
                    clearInterval(this.heartbeatInterval);
                    if (window.navigator.vibrate) window.navigator.vibrate(0); // Força a paragem
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        @keyframes shimmer { 100% { transform: translateX(100%); } }
    </style>
</body>
</html>