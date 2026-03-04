<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Diário de Combustão | Lumina</title>
    <meta name="theme-color" content="#0f172a">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Animação Mágica da Combustão do Texto */
        @keyframes burnEffect {
            0%   { color: #f8fafc; text-shadow: none; filter: blur(0); transform: translateY(0); opacity: 1; }
            20%  { color: #fb923c; text-shadow: 0 -2px 15px #ef4444, 0 0 5px #f97316; filter: blur(0.5px); transform: translateY(-1px); opacity: 0.9; }
            50%  { color: #52525b; text-shadow: 0 -5px 20px #1c1917; filter: blur(3px); transform: translateY(-10px); opacity: 0.6; }
            80%  { color: #27272a; text-shadow: none; filter: blur(8px); transform: translateY(-25px) scale(1.05); opacity: 0.2; }
            100% { color: transparent; filter: blur(12px); transform: translateY(-40px) scale(1.1); opacity: 0; }
        }

        .is-burning {
            animation: burnEffect 3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            pointer-events: none;
        }

        /* Animação do Fundo durante a queima */
        @keyframes bgBurn {
            0%   { background-color: #0f172a; }
            30%  { background-color: #450a0a; } /* Brilho de fogo */
            100% { background-color: #020617; } /* Frio e vazio */
        }
        
        .bg-is-burning {
            animation: bgBurn 3.5s ease-out forwards;
        }

        /* Esconder scrollbar na textarea mas manter funcionalidade */
        textarea::-webkit-scrollbar { display: none; }
        textarea { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans selection:bg-orange-500/30 selection:text-orange-200 transition-colors"
      x-data="burnJournal()">

    {{-- Overlay animado para background --}}
    <div class="fixed inset-0 z-0 pointer-events-none transition-opacity duration-1000"
         :class="burning ? 'bg-is-burning' : ''"></div>

    {{-- Brilho de fogo passivo no fundo (respiração leve) --}}
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-[80vw] h-[40vh] bg-orange-600/5 rounded-t-full blur-[100px] animate-pulse pointer-events-none z-0 transition-opacity duration-1000"
         x-show="!burned" x-transition:leave="opacity-0"></div>

    {{-- Navegação de Saída --}}
    <div class="relative z-20 p-6 flex justify-between items-center" x-show="!burning && !burned" x-transition.opacity>
        <a href="{{ route('calm.index') }}" class="text-slate-400 hover:text-white flex items-center gap-2 font-bold transition-colors">
            <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm">Voltar à Zona Calma</span>
        </a>
    </div>

    {{-- ECRÃ 1: A ESCRITA --}}
    <main class="flex-1 flex flex-col relative z-10 w-full max-w-3xl mx-auto px-6 pb-6" x-show="!burned">
        
        <div class="mb-6 transition-all duration-1000" :class="burning ? 'opacity-0 -translate-y-4' : 'opacity-100'">
            <h1 class="text-2xl font-black text-white flex items-center gap-2 mb-2">
                <i class="ri-fire-fill text-orange-500"></i> Diário de Combustão
            </h1>
            <p class="text-sm text-slate-400">Escreve aqui tudo o que te pesa, o que te assombra ou o que precisas de gritar. Nada disto será guardado. Ninguém vai ler.</p>
        </div>

        {{-- A Zona de Escrita --}}
        <div class="flex-1 relative mb-6">
            <textarea x-model="text" 
                      x-ref="textarea"
                      placeholder="Despeja tudo aqui..."
                      class="w-full h-full bg-transparent border-none resize-none focus:ring-0 text-xl md:text-2xl leading-relaxed text-slate-200 placeholder:text-slate-700 font-medium p-0 outline-none"
                      :class="burning ? 'is-burning' : ''"
                      :readonly="burning"></textarea>
        </div>

        {{-- O Botão de Queimar --}}
        <div class="flex justify-center shrink-0 transition-all duration-500" :class="burning ? 'opacity-0 translate-y-10 pointer-events-none' : 'opacity-100'">
            <button @click="ignite()" 
                    :disabled="text.length < 3 || burning"
                    class="group relative px-8 py-4 rounded-full font-black text-sm tracking-widest uppercase transition-all duration-300 overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="text.length >= 3 ? 'bg-orange-500 text-white shadow-[0_0_20px_rgba(249,115,22,0.4)] hover:shadow-[0_0_30px_rgba(249,115,22,0.6)] hover:bg-orange-400 hover:-translate-y-1' : 'bg-slate-800 text-slate-500'">
                
                <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_50%,transparent_75%)] w-[200%] h-[200%] animate-[shimmer_2s_infinite] -translate-x-1/2 -translate-y-1/2 pointer-events-none group-hover:block" style="display:none;"></div>
                
                <span class="relative z-10 flex items-center gap-2">
                    <i class="ri-fire-line text-lg group-hover:scale-125 transition-transform"></i> Deixar Ir
                </span>
            </button>
        </div>
    </main>

    {{-- ECRÃ 2: A CATARSE (DEPOIS DE QUEIMAR) --}}
    <main class="fixed inset-0 flex flex-col items-center justify-center z-30 p-6" x-show="burned" x-cloak x-transition:enter="transition ease-out duration-1000 delay-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
        
        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mb-8 shadow-inner shadow-black">
            <i class="ri-wind-line text-4xl text-slate-400 animate-pulse"></i>
        </div>
        
        <h2 class="text-2xl md:text-3xl font-black text-white mb-4 text-center">Transformou-se em cinza.</h2>
        <p class="text-slate-400 text-center max-w-md leading-relaxed mb-10 text-sm md:text-base">
            O que escreveste já não existe. Não está em lado nenhum a não ser no passado. O peso ficou ligeiramente menor. Respira.
        </p>

        <div class="flex flex-col sm:flex-row gap-4">
            <button @click="reset()" class="px-6 py-3 rounded-xl border border-slate-700 text-slate-300 font-bold hover:bg-slate-800 transition-colors text-sm">
                Escrever mais
            </button>
            <a href="{{ route('calm.index') }}" class="px-6 py-3 rounded-xl bg-slate-100 text-slate-900 font-black hover:bg-white transition-colors text-sm shadow-xl shadow-white/5 text-center flex items-center justify-center gap-2">
                <i class="ri-leaf-fill text-emerald-500"></i> Voltar ao Santuário
            </a>
        </div>
    </main>

    <script>
        function burnJournal() {
            return {
                text: '',
                burning: false,
                burned: false,

                ignite() {
                    if (this.text.length < 3) return;
                    
                    // Dispara a animação
                    this.burning = true;
                    
                    // Vibração háptica suave se o telemóvel suportar (aumenta o impacto psicológico)
                    if (window.navigator && window.navigator.vibrate) {
                        window.navigator.vibrate([50, 100, 150, 200, 250, 500]); // Simula o crescendo do fogo
                    }

                    // Remove o texto e muda de ecrã após o fim da animação de combustão (3 segundos)
                    setTimeout(() => {
                        this.text = ''; // Literalmente destrói o dado no frontend
                        this.burned = true;
                        this.burning = false;
                    }, 3000); 
                },

                reset() {
                    this.burned = false;
                    setTimeout(() => { this.$refs.textarea.focus(); }, 100);
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