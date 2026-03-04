<x-lumina-layout title="O Cofre de Luz | Lumina">
    
    <x-slot name="css">
        <style>
            .golden-glow {
                background: radial-gradient(circle at center, rgba(253, 224, 71, 0.15) 0%, rgba(253, 224, 71, 0) 70%);
            }
            .sparkle-anim {
                animation: sparkle 4s ease-in-out infinite;
            }
            @keyframes sparkle {
                0%, 100% { opacity: 0.5; transform: scale(0.8); }
                50% { opacity: 1; transform: scale(1.2); }
            }
            /* Esconder a carta virada de costas no modo 3D */
            .preserve-3d { transform-style: preserve-3d; perspective: 1000px; }
            .backface-hidden { backface-visibility: hidden; }
            .rotate-y-180 { transform: rotateY(180deg); }
        </style>
    </x-slot>

    {{-- Fundo quente e seguro --}}
    <div class="fixed inset-0 bg-slate-950 -z-20 pointer-events-none"></div>
    <div class="fixed inset-0 bg-gradient-to-b from-amber-900/10 via-slate-900 to-slate-950 -z-10 pointer-events-none"></div>
    <div class="fixed inset-0 golden-glow -z-10 pointer-events-none"></div>

    <div class="py-12 pt-28 md:pt-32 relative z-10" x-data="vaultOfLight()">
        <div class="max-w-4xl mx-auto px-6">

            {{-- Navegação --}}
            <div class="flex items-center justify-between mb-8">
                <a href="{{ route('calm.index') }}" class="text-amber-500/50 hover:text-amber-400 flex items-center gap-2 font-bold transition-colors">
                    <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm">Voltar</span>
                </a>
            </div>

            {{-- Cabeçalho do Cofre --}}
            <div class="text-center mb-12 relative">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-amber-500/20 rounded-full blur-[50px] pointer-events-none"></div>
                <i class="ri-sun-fill text-5xl text-amber-400 mb-4 inline-block drop-shadow-[0_0_15px_rgba(251,191,36,0.6)] sparkle-anim"></i>
                <h1 class="text-3xl md:text-4xl font-black text-white mb-3 tracking-tight">O Cofre de Luz</h1>
                <p class="text-slate-400 text-sm max-w-lg mx-auto leading-relaxed">
                    Guarda aqui os momentos em que sentiste paz, as tuas vitórias invisíveis e as palavras que te dão força. 
                    Quando a escuridão voltar, abre o cofre.
                </p>
            </div>

            {{-- Painel Principal (Grid de ações) --}}
            <div class="grid md:grid-cols-2 gap-6 mb-12">
                
                {{-- Adicionar Nova Luz --}}
                <div class="bg-slate-900/80 backdrop-blur-md border border-amber-500/20 rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden group focus-within:border-amber-500/50 transition-colors">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl group-focus-within:bg-amber-500/20 transition-colors"></div>
                    <h2 class="text-lg font-bold text-amber-100 mb-4 flex items-center gap-2"><i class="ri-quill-pen-line text-amber-500"></i> Guardar uma Memória</h2>
                    
                    <form @submit.prevent="saveLight()" class="relative z-10">
                        <textarea x-model="newLightText" rows="4" placeholder="Hoje consegui levantar-me da cama e beber um chá a olhar para a janela. Senti paz por 5 minutos." class="w-full bg-slate-950/50 border border-slate-800 text-slate-200 text-sm rounded-2xl p-4 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none resize-none placeholder-slate-600 transition-all"></textarea>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" :disabled="newLightText.trim().length === 0 || isSaving" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2" :class="newLightText.trim().length > 0 ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-[0_0_15px_rgba(251,191,36,0.3)]' : 'bg-slate-800 text-slate-500'">
                                <i class="ri-lock-fill" x-show="!isSaving"></i>
                                <i class="ri-loader-4-line animate-spin" x-show="isSaving"></i>
                                Trancar no Cofre
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Botão de Emergência: Preciso de Luz --}}
                <div class="bg-gradient-to-br from-amber-500/10 to-orange-600/10 backdrop-blur-md border border-amber-500/20 rounded-3xl p-6 md:p-8 shadow-2xl flex flex-col items-center justify-center text-center relative overflow-hidden group">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-amber-600/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
                    
                    <div class="w-16 h-16 rounded-full bg-amber-500/20 border border-amber-500/50 text-amber-400 flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform duration-500">
                        <i class="ri-flashlight-fill"></i>
                    </div>
                    <h2 class="text-xl font-bold text-amber-100 mb-2">Preciso de Luz</h2>
                    <p class="text-xs text-amber-200/60 mb-6 max-w-xs">A escuridão está forte hoje? Pede ao cofre para te lembrar de quem és realmente.</p>
                    
                    <button @click="drawLight()" :disabled="lights.length === 0" class="px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-full font-black text-sm uppercase tracking-widest shadow-[0_0_20px_rgba(251,191,36,0.4)] hover:shadow-[0_0_30px_rgba(251,191,36,0.6)] transition-all disabled:opacity-50 disabled:shadow-none">
                        Relembrar-me
                    </button>
                </div>
            </div>

            {{-- Modal de Leitura (O Cartão que se vira) --}}
            <div x-show="drawnLight" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" x-show="drawnLight" x-transition.opacity @click="drawnLight = null"></div>
                
                <div class="relative w-full max-w-sm preserve-3d" x-show="drawnLight" x-transition:enter="transition duration-700 ease-out" x-transition:enter-start="opacity-0 translate-y-12 rotate-12" x-transition:enter-end="opacity-100 translate-y-0 rotate-0">
                    
                    {{-- A Memória (Frente do Cartão) --}}
                    <div class="bg-amber-50 rounded-[2rem] p-8 shadow-2xl shadow-amber-500/20 border border-amber-200 relative overflow-hidden backface-hidden">
                        <i class="ri-double-quotes-l text-6xl text-amber-500/20 absolute -top-2 -left-2"></i>
                        <p class="text-amber-900 text-lg font-medium leading-relaxed relative z-10 text-center italic" x-text="drawnLight?.content"></p>
                        <div class="mt-8 text-center relative z-10 border-t border-amber-200/50 pt-4">
                            <p class="text-xs font-bold uppercase tracking-widest text-amber-600 mb-1">A tua própria voz</p>
                            <p class="text-[10px] text-amber-700/60" x-text="drawnLight?.date"></p>
                        </div>
                        <button @click="drawnLight = null" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Contador de Luzes (Opcional/Visual) --}}
            <div class="text-center">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="ri-safe-line"></i> <span x-text="lights.length">0</span> memórias guardadas a salvo
                </p>
            </div>

        </div>
    </div>

    <script>
        function vaultOfLight() {
            return {
                newLightText: '',
                isSaving: false,
                drawnLight: null,
                // Mock array (O Claude ligará isto à BD)
                lights: [
                    { id: 1, content: 'Hoje a chuva lá fora deu-me paz. Consegui respirar fundo e não pensar no amanhã.', date: '12 Fev, 2026' }
                ],

                async saveLight() {
                    if(this.newLightText.trim() === '') return;
                    this.isSaving = true;
                    
                    try {
                        // O Claude fará a rota de gravação
                        // const res = await axios.post('/zona-calma/cofre', { content: this.newLightText });
                        
                        // Simulação
                        setTimeout(() => {
                            this.lights.unshift({
                                id: Date.now(),
                                content: this.newLightText,
                                date: 'Agora mesmo'
                            });
                            this.newLightText = '';
                            this.isSaving = false;
                            
                            if(window.navigator && window.navigator.vibrate) window.navigator.vibrate([30, 50, 30]);
                        }, 800);

                    } catch (e) {
                        this.isSaving = false;
                    }
                },

                drawLight() {
                    if(this.lights.length === 0) return;
                    
                    // Escolhe uma luz aleatória do array
                    const randomIndex = Math.floor(Math.random() * this.lights.length);
                    this.drawnLight = this.lights[randomIndex];
                    
                    // Vibração de impacto emocional
                    if(window.navigator && window.navigator.vibrate) window.navigator.vibrate([100, 50, 200]);
                }
            }
        }
    </script>
</x-lumina-layout>