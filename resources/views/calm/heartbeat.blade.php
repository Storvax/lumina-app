<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Sintonia | Lumina</title>
    <meta name="theme-color" content="#4c0519">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-rose-950 text-rose-100 min-h-screen flex flex-col font-sans overflow-hidden selection:bg-rose-500/30 selection:text-rose-200"
      x-data="communityHeartbeat()">

    {{-- Fundo Dinâmico --}}
    <div class="absolute inset-0 bg-gradient-to-b from-rose-900/20 to-rose-950 -z-20"></div>
    
    {{-- Navegação --}}
    <div class="relative z-20 p-6 flex justify-between items-center transition-opacity duration-700" :class="isActive ? 'opacity-0 pointer-events-none' : 'opacity-100'">
        <a href="{{ route('calm.index') }}" class="text-rose-500 hover:text-white flex items-center gap-2 font-bold transition-colors">
            <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm">Voltar ao Santuário</span>
        </a>
    </div>

    <main class="flex-1 flex flex-col items-center justify-center relative z-10 w-full max-w-md mx-auto px-6 pb-20">
        
        {{-- Mensagem Inicial --}}
        <div class="text-center absolute top-10 w-full transition-opacity duration-700" :class="isActive ? 'opacity-0' : 'opacity-100'">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-black uppercase tracking-widest mb-6">
                <i class="ri-pulse-line"></i> Co-Regulação Somática
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white mb-4">O Coração da Comunidade</h1>
            <p class="text-rose-300/80 text-sm max-w-xs mx-auto leading-relaxed">
                Quando a ansiedade isola, lembra-te que nunca estás só. Liga a vibração e encosta o telemóvel ao teu peito.
            </p>
        </div>

        {{-- Coração Central (Visual) --}}
        <div class="relative w-64 h-64 flex items-center justify-center mt-8">
            {{-- Brilho Expansivo do Batimento --}}
            <div class="absolute inset-0 bg-rose-600/20 rounded-full blur-3xl transition-transform duration-300"
                 :class="isBeating ? 'scale-150 opacity-80' : 'scale-100 opacity-30'"></div>
            
            <div class="absolute inset-10 bg-rose-500/30 rounded-full blur-xl transition-transform duration-200"
                 :class="isBeating ? 'scale-125 opacity-100' : 'scale-100 opacity-20'"></div>

            {{-- Ícone Físico --}}
            <div class="relative z-10 flex items-center justify-center transition-transform duration-200"
                 :class="isBeating ? 'scale-125 text-rose-400' : 'scale-100 text-rose-900 drop-shadow-[0_0_30px_rgba(225,29,72,0.4)]'">
                 <i class="ri-heart-pulse-fill text-8xl md:text-9xl filter drop-shadow-[0_0_15px_rgba(225,29,72,0.8)]"></i>
            </div>
        </div>

        {{-- Mensagem Imersiva (Aparece quando ativo) --}}
        <div class="absolute bottom-40 text-center w-full transition-opacity duration-1000 delay-500" x-show="isActive" x-cloak>
            <p class="text-lg font-serif italic text-rose-200 mb-2">Não estás sozinho.</p>
            <p class="text-xs text-rose-400/60 uppercase tracking-widest font-bold">
                <span class="text-rose-400" x-text="onlineCount">24</span> pessoas connosco agora.
            </p>
        </div>

        {{-- Botão de Ação --}}
        <div class="absolute bottom-12 w-full px-6 flex flex-col items-center gap-4">
            
            <button @click="toggleHeartbeat()" 
                    class="w-full max-w-[250px] py-4 rounded-full font-black text-sm tracking-widest uppercase transition-all duration-500 shadow-xl overflow-hidden relative group"
                    :class="isActive ? 'bg-rose-900/50 text-rose-500 border border-rose-900 hover:bg-rose-900' : 'bg-rose-600 text-white hover:bg-rose-500 hover:-translate-y-1 shadow-[0_10px_30px_rgba(225,29,72,0.4)]'">
                
                {{-- Efeito de luz ao passar o rato --}}
                <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_50%,transparent_75%)] w-[200%] h-[200%] animate-[shimmer_2s_infinite] -translate-x-1/2 -translate-y-1/2 pointer-events-none group-hover:block" style="display:none;" x-show="!isActive"></div>

                <span class="relative z-10 flex items-center justify-center gap-2">
                    <i :class="isActive ? 'ri-stop-mini-fill' : 'ri-play-fill'" class="text-lg"></i>
                    <span x-text="isActive ? 'Parar' : 'Sintonizar'"></span>
                </span>
            </button>
            
            <p class="text-[10px] text-rose-500/50" x-show="!isActive"><i class="ri-smartphone-line"></i> Requer motor de vibração ativo</p>
        </div>

    </main>

    <script>
        function communityHeartbeat() {
            return {
                isActive: false,
                isBeating: false,
                heartbeatInterval: null,
                onlineCount: Math.floor(Math.random() * (45 - 12 + 1)) + 12, // Simula pessoas online se não houver backend em tempo real

                toggleHeartbeat() {
                    if (this.isActive) {
                        this.stop();
                    } else {
                        this.start();
                    }
                },

                start() {
                    // Verifica se o browser suporta vibração
                    if (!window.navigator || !window.navigator.vibrate) {
                        alert('O teu dispositivo não suporta feedback háptico (vibração) no browser.');
                        return;
                    }

                    this.isActive = true;
                    
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
                    window.navigator.vibrate([80, 150, 40]); // Vibra 80ms, pausa 150ms, vibra 40ms
                    
                    setTimeout(() => {
                        this.isBeating = false;
                    }, 300); // O visual volta ao normal
                },

                stop() {
                    this.isActive = false;
                    this.isBeating = false;
                    clearInterval(this.heartbeatInterval);
                    if (window.navigator.vibrate) window.navigator.vibrate(0);
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