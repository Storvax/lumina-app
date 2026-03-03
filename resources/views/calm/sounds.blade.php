<x-lumina-layout title="Sons de Portugal | Lumina">
    <div class="py-12 pt-28 md:pt-32 min-h-screen relative pb-32" x-data="soundPlayer()">
        <div class="max-w-5xl mx-auto px-6 relative z-10">

            <x-emotional-breadcrumb :items="[['label' => 'Zona Calma', 'route' => 'calm.index'], ['label' => 'Sons de Portugal']]" />

            <div class="mb-10 mt-4">
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <i class="ri-headphone-line text-indigo-500"></i> Sons de Portugal
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-base mt-2 max-w-xl leading-relaxed">
                    Paisagens sonoras gravadas no nosso país. Põe os fones, fecha os olhos e deixa-te levar para um lugar seguro.
                </p>
            </div>

            {{-- Categorias / Grelha de Sons --}}
            <div class="space-y-12">
                @foreach(config('sound-library.categories', []) as $key => $category)
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-6">
                            <span class="w-8 h-8 rounded-full bg-{{ $category['color'] }}-100 dark:bg-{{ $category['color'] }}-900/30 text-{{ $category['color'] }}-600 flex items-center justify-center text-sm">
                                <i class="{{ $category['icon'] }}"></i>
                            </span>
                            {{ $category['name'] }}
                        </h2>
                        
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach($category['sounds'] as $sound)
                                <button @click="playSound('{{ $sound['id'] }}', '{{ $sound['name'] }}', '{{ $category['name'] }}', '{{ asset($sound['file']) }}')"
                                        class="group relative bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-100 dark:border-slate-700 hover:shadow-xl hover:-translate-y-1 transition-all text-left overflow-hidden"
                                        :class="currentSound?.id === '{{ $sound['id'] }}' ? 'ring-2 ring-indigo-500 shadow-lg border-transparent dark:border-transparent' : ''">
                                    
                                    {{-- Fundo animado se estiver a tocar --}}
                                    <div x-show="currentSound?.id === '{{ $sound['id'] }}' && isPlaying" x-transition class="absolute inset-0 bg-indigo-50/50 dark:bg-indigo-900/20"></div>

                                    <div class="relative z-10 flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors shadow-sm"
                                                 :class="currentSound?.id === '{{ $sound['id'] }}' ? 'bg-indigo-600 text-white' : 'bg-slate-50 dark:bg-slate-700 text-slate-400 group-hover:text-indigo-500'">
                                                <i :class="(currentSound?.id === '{{ $sound['id'] }}' && isPlaying) ? 'ri-equalizer-line animate-pulse' : 'ri-play-fill'" class="text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $sound['name'] }}</p>
                                                <p class="text-[10px] font-medium text-slate-400 dark:text-slate-500 mt-0.5 tracking-wider uppercase"><i class="ri-timer-line"></i> {{ $sound['duration'] ?? 'Loop' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- LEITOR FLUTUANTE ESTILO PREMIUM (Aparece no fundo do ecrã) --}}
        <div x-show="currentSound" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-full"
             style="display: none;"
             class="fixed bottom-24 md:bottom-8 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-full md:max-w-2xl z-50">
             
            <div class="bg-slate-900/95 backdrop-blur-xl border border-slate-700 shadow-2xl rounded-3xl p-4 md:p-5 text-white flex items-center gap-4">
                
                {{-- Botão Play/Pause principal --}}
                <button @click="togglePlay()" class="w-14 h-14 shrink-0 rounded-full bg-white text-slate-900 hover:scale-105 active:scale-95 transition-transform flex items-center justify-center shadow-lg">
                    <i :class="isPlaying ? 'ri-pause-fill' : 'ri-play-fill'" class="text-2xl"></i>
                </button>
                
                {{-- Info da Música e Ondas Sonoras --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm md:text-base font-bold text-white truncate" x-text="currentSound?.name"></p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-indigo-300 truncate" x-text="currentSound?.category"></span>
                        
                        {{-- Barras de áudio animadas (CSS puro) --}}
                        <div class="flex items-end gap-0.5 h-3 ml-2 opacity-70" x-show="isPlaying">
                            <div class="w-1 bg-indigo-400 rounded-t-sm animate-[soundWave_1.2s_ease-in-out_infinite]"></div>
                            <div class="w-1 bg-indigo-400 rounded-t-sm animate-[soundWave_0.8s_ease-in-out_infinite_0.1s]"></div>
                            <div class="w-1 bg-indigo-400 rounded-t-sm animate-[soundWave_1.0s_ease-in-out_infinite_0.2s]"></div>
                            <div class="w-1 bg-indigo-400 rounded-t-sm animate-[soundWave_0.9s_ease-in-out_infinite_0.3s]"></div>
                        </div>
                    </div>
                </div>

                {{-- Controlo de Volume (Desktop) --}}
                <div class="hidden md:flex items-center gap-2 px-4 border-l border-slate-700">
                    <i class="ri-volume-down-line text-slate-400 text-lg"></i>
                    <input type="range" min="0" max="1" step="0.05" x-model="volume" @input="updateVolume()"
                           class="w-24 accent-indigo-500 bg-slate-700 h-1.5 rounded-full appearance-none outline-none cursor-pointer">
                </div>
                
                {{-- Fechar Player --}}
                <button @click="stopSound()" class="w-10 h-10 shrink-0 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors flex items-center justify-center ml-2 md:ml-0 border border-slate-700">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function soundPlayer() {
                return {
                    audio: null,
                    currentSound: null,
                    isPlaying: false,
                    volume: 0.6,

                    playSound(id, name, category, url) {
                        if (this.currentSound?.id === id) {
                            this.togglePlay();
                            return;
                        }
                        if (this.audio) { this.audio.pause(); }
                        this.audio = new Audio(url);
                        this.audio.volume = this.volume;
                        this.audio.loop = true;
                        this.audio.play().catch(e => console.error("Erro ao tocar áudio:", e));
                        this.currentSound = { id, name, category };
                        this.isPlaying = true;
                    },

                    togglePlay() {
                        if (!this.audio) return;
                        if (this.isPlaying) { this.audio.pause(); }
                        else { this.audio.play(); }
                        this.isPlaying = !this.isPlaying;
                    },

                    stopSound() {
                        if (this.audio) {
                            this.audio.pause();
                            this.audio.currentTime = 0;
                            this.audio = null;
                        }
                        this.currentSound = null;
                        this.isPlaying = false;
                    },

                    updateVolume() {
                        if (this.audio) this.audio.volume = this.volume;
                    }
                }
            }
        </script>
        
        <style>
            /* Animação das barras de som */
            @keyframes soundWave {
                0%, 100% { height: 3px; }
                50% { height: 12px; }
            }
            
            /* Customizar o input range de volume para navegadores Webkit */
            input[type=range]::-webkit-slider-thumb {
                appearance: none;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #6366f1;
                cursor: pointer;
            }
        </style>
    </x-slot>
</x-lumina-layout>