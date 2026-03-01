<x-lumina-layout title="Sons de Portugal | Lumina">
    <div class="py-12 pt-32" x-data="soundPlayer()">
        <div class="max-w-5xl mx-auto px-6">

            <x-emotional-breadcrumb :items="[['label' => 'Zona Calma', 'route' => 'calm.index'], ['label' => 'Sons de Portugal']]" />

            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                    <i class="ri-music-2-line text-indigo-500"></i> Sons de Portugal
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Paisagens sonoras inspiradas em Portugal. Fecha os olhos e deixa-te levar.</p>
            </div>

            {{-- Player ativo --}}
            <div x-show="currentSound" x-transition
                 class="mb-8 bg-gradient-to-r from-indigo-50/80 to-violet-50/80 dark:from-indigo-900/20 dark:to-violet-900/20 rounded-3xl p-6 border border-indigo-100 dark:border-indigo-800">
                <div class="flex items-center gap-4">
                    <button @click="togglePlay()" class="w-14 h-14 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-lg transition-all active:scale-95">
                        <i :class="isPlaying ? 'ri-pause-fill' : 'ri-play-fill'" class="text-2xl"></i>
                    </button>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="currentSound?.name"></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="currentSound?.category"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="ri-volume-down-line text-slate-400"></i>
                        <input type="range" min="0" max="1" step="0.1" x-model="volume" @input="updateVolume()"
                               class="w-20 accent-indigo-600">
                        <i class="ri-volume-up-line text-slate-400"></i>
                    </div>
                    <button @click="stopSound()" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Categorias --}}
            @foreach(config('sound-library.categories') as $key => $category)
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                        <i class="{{ $category['icon'] }} text-{{ $category['color'] }}-500"></i>
                        {{ $category['name'] }}
                    </h2>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($category['sounds'] as $sound)
                            <button @click="playSound('{{ $sound['id'] }}', '{{ $sound['name'] }}', '{{ $category['name'] }}', '{{ asset($sound['file']) }}')"
                                    class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 hover:shadow-md hover:border-{{ $category['color'] }}-200 dark:hover:border-{{ $category['color'] }}-700 transition-all text-left"
                                    :class="currentSound?.id === '{{ $sound['id'] }}' ? 'ring-2 ring-{{ $category['color'] }}-500/30 border-{{ $category['color'] }}-200' : ''">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-{{ $category['color'] }}-50 dark:bg-{{ $category['color'] }}-900/30 flex items-center justify-center text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400">
                                        <i :class="currentSound?.id === '{{ $sound['id'] }}' && isPlaying ? 'ri-equalizer-line animate-pulse' : 'ri-play-mini-fill'" class="text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $sound['name'] }}</p>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500">{{ $sound['duration'] }}</p>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function soundPlayer() {
                return {
                    audio: null,
                    currentSound: null,
                    isPlaying: false,
                    volume: 0.7,

                    playSound(id, name, category, url) {
                        if (this.currentSound?.id === id) {
                            this.togglePlay();
                            return;
                        }
                        if (this.audio) this.audio.pause();
                        this.audio = new Audio(url);
                        this.audio.volume = this.volume;
                        this.audio.loop = true;
                        this.audio.play();
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
                        if (this.audio) { this.audio.pause(); this.audio = null; }
                        this.currentSound = null;
                        this.isPlaying = false;
                    },

                    updateVolume() {
                        if (this.audio) this.audio.volume = this.volume;
                    }
                }
            }
        </script>
    </x-slot>
</x-lumina-layout>
