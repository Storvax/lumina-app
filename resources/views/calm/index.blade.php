<x-lumina-layout title="Zona Calma | Lumina">
    <div class="py-12 pt-32">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <div class="mb-12 text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 dark:text-white flex items-center justify-center md:justify-start gap-3">
                    <i class="ri-leaf-line text-emerald-500"></i> O Teu Santuário
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">Um espaço seguro para abrandar, respirar e voltar ao momento presente.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-12">
                
                <a href="{{ route('calm.grounding') }}" class="group bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-3xl mb-6"><i class="ri-focus-2-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Grounding 5-4-3-2-1</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Técnica guiada para travar a ansiedade e voltar ao corpo e ao presente.</p>
                        <span class="text-emerald-600 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Iniciar Exercício <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('calm.crisis') }}" class="group bg-rose-50 dark:bg-slate-800 rounded-3xl p-8 border border-rose-100 dark:border-rose-900/30 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-100/50 dark:bg-rose-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-rose-200/50 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center text-3xl mb-6"><i class="ri-alarm-warning-line"></i></div>
                        <h3 class="text-xl font-bold text-rose-900 dark:text-white mb-2">Modo Crise</h3>
                        <p class="text-rose-700/70 dark:text-slate-400 text-sm mb-4">Um ecrã escuro, sem distrações, apenas com o teu plano de segurança.</p>
                        <span class="text-rose-600 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ativar Agora <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <button onclick="alert('Funcionalidade de sons a integrar!')" class="group bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all relative overflow-hidden text-left">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-3xl mb-6"><i class="ri-rainy-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Paisagens Sonoras</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Ouve o som da chuva, lareira ou ondas do mar em loop.</p>
                        <span class="text-blue-600 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ouvir <i class="ri-play-circle-line"></i></span>
                    </div>
                </button>

            </div>

            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 md:p-10 border border-slate-100 dark:border-slate-700 shadow-sm">
                <div class="flex flex-col md:flex-row justify-between md:items-end gap-6 mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-disc-line text-indigo-500"></i> Playlist da Comunidade
                        </h2>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Músicas que ajudam os outros a acalmar. Vota na tua favorita.</p>
                    </div>
                    
                    <button onclick="document.getElementById('suggest-song-modal').classList.remove('hidden')" class="px-5 py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-xl font-bold hover:bg-indigo-100 transition-colors flex items-center justify-center gap-2">
                        <i class="ri-add-line"></i> Sugerir Música
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($songs as $index => $song)
                        <div class="flex items-center justify-between p-4 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border border-transparent hover:border-slate-100 dark:hover:border-slate-600">
                            <div class="flex items-center gap-4">
                                <span class="text-slate-300 dark:text-slate-600 font-black text-xl w-6 text-center">{{ $index + 1 }}</span>
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white">{{ $song->title }}</p>
                                    <p class="text-xs text-slate-500">{{ $song->artist }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                @if($song->spotify_url)
                                    <a href="{{ $song->spotify_url }}" target="_blank" class="text-slate-400 hover:text-green-500 transition-colors" title="Ouvir no Spotify"><i class="ri-spotify-fill text-2xl"></i></a>
                                @endif
                                
                                <form action="{{ route('calm.playlist.vote', $song) }}" method="POST">
                                    @csrf
                                    <button class="flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                        <i class="ri-arrow-up-s-line leading-none text-lg"></i>
                                        <span class="text-[10px] font-bold leading-none">{{ $song->votes_count }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="ri-music-2-line text-4xl text-slate-300 mb-2"></i>
                            <p class="text-slate-500">A playlist está vazia. Sê o primeiro a sugerir uma música calma!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <div id="suggest-song-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-2 dark:text-white">Partilhar Música</h3>
            <p class="text-sm text-slate-500 mb-6">Que música te ajuda a relaxar nos momentos mais difíceis?</p>
            
            <form action="{{ route('calm.playlist.suggest') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Título da Música</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Artista</label>
                    <input type="text" name="artist" required class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Link Spotify (Opcional)</label>
                    <input type="url" name="spotify_url" placeholder="https://open.spotify.com/track/..." class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500">
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('suggest-song-modal').classList.add('hidden')" class="px-5 py-2.5 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all">Adicionar à Playlist</button>
                </div>
            </form>
        </div>
    </div>
</x-lumina-layout>