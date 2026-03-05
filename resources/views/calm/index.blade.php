<x-lumina-layout title="Zona Calma | Lumina">
    
    <x-slot name="css">
        /* Customização Suave do Tour (Onboarding) - Igual ao do Mural para coerência visual */
        .lumina-tour-theme {
            border-radius: 1.5rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #f1f5f9 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            padding: 20px !important;
        }
        .driver-popover-title { font-weight: 800 !important; color: #1e293b !important; font-size: 1.1rem !important; margin-bottom: 8px !important; }
        .driver-popover-description { color: #64748b !important; font-size: 0.9rem !important; line-height: 1.5 !important;}
        .driver-popover-footer button { border-radius: 0.75rem !important; font-weight: 700 !important; text-shadow: none !important; }
        .driver-popover-next-btn { background-color: #10b981 !important; color: white !important; text-shadow: none !important; border: none !important; padding: 8px 16px !important; }
        .driver-popover-prev-btn { background-color: #f1f5f9 !important; color: #64748b !important; border: none !important; padding: 8px 16px !important;}
        .driver-popover-close-btn { color: #94a3b8 !important; top: 15px !important; right: 15px !important; }
        
        /* Tema escuro para o Tour */
        .dark .lumina-tour-theme { background-color: #1e293b !important; border-color: #334155 !important; }
        .dark .driver-popover-title { color: #f8fafc !important; }
        .dark .driver-popover-description { color: #cbd5e1 !important; }
        .dark .driver-popover-prev-btn { background-color: #334155 !important; color: #cbd5e1 !important; }
    </x-slot>

    <div class="py-12 pt-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <x-emotional-breadcrumb :items="[['label' => 'Zona Calma']]" />

            <x-contextual-tip
                feature="calm-zone"
                title="Respira Fundo"
                description="Explora exercícios de respiração, sons ambiente e ferramentas para te acalmar."
                icon="ri-leaf-line"
            />

            <div class="mb-10 text-center md:text-left">
                <div class="relative inline-block">
                    <h1 class="text-3xl md:text-4xl font-black text-slate-800 dark:text-white flex items-center justify-center md:justify-start gap-3">
                        <i class="ri-leaf-line text-emerald-500"></i> O Teu Santuário
                    </h1>
                    
                    {{-- Botão discreto para chamar o tutorial de volta --}}
                    <button onclick="window.startCalmTour()" 
                            class="absolute -right-12 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all flex items-center justify-center text-sm" 
                            title="Como funciona a Zona Calma?" aria-label="Ver tutorial da Zona Calma">
                        <i class="ri-question-mark"></i>
                    </button>
                </div>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-base md:text-lg">Um espaço seguro para abrandar, respirar e voltar ao momento presente.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-12">
                {{-- Adicionado o ID: card-grounding --}}
                <a href="{{ route('calm.grounding') }}" id="card-grounding" class="group bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-4 md:mb-6"><i class="ri-focus-2-line"></i></div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-800 dark:text-white mb-2">Grounding 5-4-3-2-1</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs md:text-sm mb-4">Técnica guiada para travar a ansiedade e voltar ao corpo.</p>
                        <span class="text-emerald-600 font-bold text-xs md:text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Iniciar Exercício <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                {{-- Adicionado o ID: card-crisis --}}
                <a href="{{ route('calm.crisis') }}" id="card-crisis" class="group bg-rose-50 dark:bg-slate-800 rounded-3xl p-6 md:p-8 border border-rose-100 dark:border-rose-900/30 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-100/50 dark:bg-rose-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-rose-200/50 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-4 md:mb-6"><i class="ri-alarm-warning-line"></i></div>
                        <h3 class="text-lg md:text-xl font-bold text-rose-900 dark:text-white mb-2">Modo Crise</h3>
                        <p class="text-rose-700/70 dark:text-slate-400 text-xs md:text-sm mb-4">Ecrã escuro, sem distrações, apenas o teu plano de segurança.</p>
                        <span class="text-rose-600 font-bold text-xs md:text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ativar Agora <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('calm.sounds') }}" class="group bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all relative overflow-hidden text-left w-full block">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-4 md:mb-6"><i class="ri-rainy-line"></i></div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-800 dark:text-white mb-2">Paisagens Sonoras</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs md:text-sm mb-4">Ouve o som da chuva, lareira ou ondas de Portugal.</p>
                        <span class="text-blue-600 font-bold text-xs md:text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ouvir <i class="ri-play-circle-line"></i></span>
                    </div>
                </a>
            </div>

            {{-- Adicionado o ID: section-playlist --}}
            <div id="section-playlist" class="bg-white dark:bg-slate-800 rounded-[2rem] md:rounded-[2.5rem] p-5 md:p-10 border border-slate-100 dark:border-slate-700 shadow-sm">
                <div class="flex flex-col md:flex-row justify-between md:items-end gap-4 mb-6 md:mb-8">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-disc-line text-indigo-500"></i> Playlist da Comunidade
                        </h2>
                        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm md:text-base">Músicas que ajudam os outros a acalmar. Vota na tua favorita.</p>
                    </div>
                    
                    <button onclick="document.getElementById('suggest-song-modal').classList.remove('hidden')" class="px-5 py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-xl font-bold hover:bg-indigo-100 transition-colors flex items-center justify-center gap-2 text-sm md:text-base w-full md:w-auto">
                        <i class="ri-add-line"></i> Sugerir Música
                    </button>
                </div>

                <div id="playlist-container" class="space-y-2 md:space-y-3">
                    @forelse($songs as $index => $song)
                        @php $hasVoted = is_array($userVotes) && in_array($song->id, $userVotes); @endphp
                        <div class="flex items-center justify-between p-3 md:p-4 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border border-transparent hover:border-slate-100 dark:hover:border-slate-600 relative group gap-2">
                            
                            <div class="flex items-center gap-3 md:gap-4 min-w-0 flex-1">
                                <span class="text-slate-300 dark:text-slate-600 font-black text-lg md:text-xl w-5 md:w-6 text-center shrink-0">{{ $index + 1 }}</span>
                                
                                @if($song->cover_url)
                                    <img src="{{ $song->cover_url }}" alt="Capa" class="w-12 h-12 md:w-14 md:h-14 rounded-xl object-cover shadow-sm border border-slate-100 dark:border-slate-700 shrink-0">
                                @else
                                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-300 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 shrink-0"><i class="ri-music-2-line text-xl md:text-2xl"></i></div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-slate-800 dark:text-white truncate text-sm md:text-base" title="{{ $song->title }}">{{ $song->title }}</p>
                                    <p class="text-xs text-slate-500 truncate" title="{{ $song->artist }}">{{ $song->artist }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1.5 md:gap-4 shrink-0">
                                @if($song->user_id === Auth::id() || (Auth::check() && Auth::user()->role === 'admin'))
                                    <button onclick="deleteSong({{ $song->id }}, this)" class="text-slate-300 hover:text-rose-500 transition-colors p-1.5 md:p-2" title="Remover música">
                                        <i class="ri-delete-bin-line text-base md:text-lg"></i>
                                    </button>
                                @endif

                                @if($song->spotify_url)
                                    <a href="{{ $song->spotify_url }}" target="_blank" class="text-slate-400 hover:text-green-500 transition-colors p-1.5 md:p-2" title="Ouvir no Spotify"><i class="ri-spotify-fill text-xl md:text-2xl"></i></a>
                                @endif
                                
                                <button onclick="voteSong({{ $song->id }}, this)" 
                                        class="flex flex-col items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-xl transition-colors {{ $hasVoted ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 hover:bg-indigo-50 hover:text-indigo-600' }}">
                                    <i class="ri-arrow-up-s-line leading-none text-base md:text-lg transition-transform"></i>
                                    <span class="text-[9px] md:text-[10px] font-bold leading-none vote-count">{{ $song->votes_count }}</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div id="empty-playlist-msg" class="text-center py-8">
                            <i class="ri-music-2-line text-4xl text-slate-300 mb-2 block"></i>
                            <p class="text-slate-500 text-sm md:text-base">A playlist está vazia. Sê o primeiro a sugerir uma música calma!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <div id="suggest-song-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-2 dark:text-white">Partilhar Música</h3>
            <p class="text-sm text-slate-500 mb-6">Podes preencher apenas o link do Spotify, ou escrever só o Nome e Artista. Nós encontramos a capa!</p>
            
            <form id="suggest-song-form" action="{{ route('calm.playlist.suggest') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Título da Música</label>
                    <input type="text" name="title" class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Artista</label>
                    <input type="text" name="artist" class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500 text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Link Spotify (Opcional, mas ajuda a buscar a capa!)</label>
                    <input type="url" name="spotify_url" placeholder="https://open.spotify.com/..." class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500 text-sm md:text-base">
                </div>
                
                <div class="flex justify-end gap-2 md:gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('suggest-song-modal').classList.add('hidden')" class="px-4 md:px-5 py-2.5 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors text-sm md:text-base">Cancelar</button>
                    <button type="submit" class="px-4 md:px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all flex items-center gap-2 text-sm md:text-base">
                        <span>Adicionar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            /**
             * TOUR DE ONBOARDING - DRIVER.JS
             */
            @auth
            window.startCalmTour = function() {
                if (!window.driver) return;

                const isMobile = window.innerWidth < 768;

                const driverObj = window.driver({
                    showProgress: true,
                    smoothScroll: true,
                    overlayColor: 'rgba(255, 255, 255, 0.7)',
                    nextBtnText: 'Continuar &rarr;',
                    prevBtnText: '&larr; Voltar',
                    doneBtnText: 'Entendido',
                    progressText: '@{{current}} de @{{total}}',
                    popoverClass: 'lumina-tour-theme',
                    steps: [
                        {
                            element: '#card-grounding',
                            popover: {
                                title: 'Volta ao momento presente',
                                description: 'Sentes a ansiedade a subir? Este exercício guiado (5-4-3-2-1) ajuda-te a abrandar os pensamentos e a reconectar com o teu corpo.',
                                side: "bottom", align: 'start'
                            }
                        },
                        {
                            element: '#card-crisis',
                            popover: {
                                title: 'O teu botão de emergência',
                                description: 'O Modo Crise escurece o ecrã, remove as distrações e foca-se apenas no teu Plano de Segurança e nas Linhas de Apoio.',
                                side: "bottom", align: isMobile ? 'center' : 'start'
                            }
                        },
                        {
                            element: '#section-playlist',
                            popover: {
                                title: 'Ouvir e Partilhar',
                                description: 'Uma playlist construída pela comunidade com músicas que trazem paz. Ouve o que ajuda os outros e partilha as tuas próprias sugestões!',
                                side: "top", align: 'center'
                            }
                        }
                    ],
                    onDestroyStarted: () => {
                        axios.post('{{ route("tour.completed") }}', { tour: 'calm_zone' })
                             .catch(err => console.error(err));
                        driverObj.destroy();
                    }
                });

                driverObj.drive();
            };

            document.addEventListener('DOMContentLoaded', () => {
                const toursCompleted = @json(Auth::user()->onboarding_tours ?? []);
                
                if (!toursCompleted['calm_zone']) {
                    setTimeout(() => {
                        window.startCalmTour();
                    }, 800);
                }
            });
            @endauth

            const form = document.getElementById('suggest-song-form');
            if(form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const btn = this.querySelector('button[type="submit"]');
                    const btnText = btn.querySelector('span');
                    const originalText = btnText.innerHTML;
                    
                    btnText.innerHTML = '<i class="ri-loader-4-line animate-spin text-lg"></i> a procurar...';
                    btn.disabled = true;

                    try {
                        const formData = new FormData(this);
                        const response = await axios.post(this.action, formData, {
                            headers: { 'Accept': 'application/json' }
                        });

                        if(response.data.success) {
                            document.getElementById('suggest-song-modal').classList.add('hidden');
                            this.reset();

                            Swal.fire({
                                title: 'Adicionada!',
                                text: response.data.message,
                                icon: 'success',
                                customClass: { popup: 'rounded-3xl' },
                                timer: 2000,
                                showConfirmButton: false
                            });

                            const emptyMsg = document.getElementById('empty-playlist-msg');
                            if (emptyMsg) emptyMsg.remove();

                            const song = response.data.song;
                            const container = document.getElementById('playlist-container');
                            
                            const coverHtml = song.cover_url 
                                ? `<img src="${song.cover_url}" alt="Capa" class="w-12 h-12 md:w-14 md:h-14 rounded-xl object-cover shadow-sm border border-slate-100 dark:border-slate-700 shrink-0">`
                                : `<div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-300 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 shrink-0"><i class="ri-music-2-line text-xl md:text-2xl"></i></div>`;

                            const currentUserId = {{ Auth::id() ?? 'null' }};
                            const deleteBtnHtml = `<button onclick="deleteSong(${song.id}, this)" class="text-slate-300 hover:text-rose-500 transition-colors p-1.5 md:p-2"><i class="ri-delete-bin-line text-base md:text-lg"></i></button>`;

                            const newHtml = `
                                <div class="flex items-center justify-between p-3 md:p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 bg-indigo-50/50 dark:bg-indigo-900/20 animate-fade-up mt-2 relative group gap-2">
                                    <div class="flex items-center gap-3 md:gap-4 min-w-0 flex-1">
                                        <span class="text-indigo-400 font-black text-[10px] md:text-xs w-5 md:w-6 text-center uppercase tracking-widest shrink-0">Novo</span>
                                        ${coverHtml}
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-800 dark:text-white truncate text-sm md:text-base">${song.title}</p>
                                            <p class="text-xs text-slate-500 truncate">${song.artist}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 md:gap-4 shrink-0">
                                        ${deleteBtnHtml}
                                        ${song.spotify_url ? `<a href="${song.spotify_url}" target="_blank" class="text-slate-400 hover:text-green-500 transition-colors p-1.5 md:p-2"><i class="ri-spotify-fill text-xl md:text-2xl"></i></a>` : ''}
                                        <button onclick="voteSong(${song.id}, this)" class="flex flex-col items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                            <i class="ri-arrow-up-s-line leading-none text-base md:text-lg transition-transform"></i>
                                            <span class="text-[9px] md:text-[10px] font-bold leading-none vote-count">0</span>
                                        </button>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML('afterbegin', newHtml);
                        }
                    } catch (error) {
                        let msg = 'Não foi possível adicionar a música. Tenta de novo.';
                        if (error.response && error.response.status === 422) {
                            msg = error.response.data.message || 'Preenche o nome OU o link do Spotify.';
                        }
                        Swal.fire({ title: 'Atenção', text: msg, icon: 'warning', customClass: { popup: 'rounded-3xl' } });
                    } finally {
                        btnText.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            }

            window.voteSong = async function(songId, btn) {
                const icon = btn.querySelector('i');
                icon.classList.add('-translate-y-1', 'scale-125');
                setTimeout(() => icon.classList.remove('-translate-y-1', 'scale-125'), 200);

                try {
                    const response = await axios.post(`/zona-calma/playlist/${songId}/votar`, {}, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if(response.data.success) {
                        const countSpan = btn.querySelector('.vote-count');
                        countSpan.innerText = response.data.votes_count;

                        if(response.data.action === 'added') {
                            btn.classList.replace('bg-slate-100', 'bg-indigo-100');
                            btn.classList.add('text-indigo-600');
                            if(btn.classList.contains('dark:bg-slate-700')) btn.classList.replace('dark:bg-slate-700', 'dark:bg-indigo-900/50');
                        } else {
                            btn.classList.replace('bg-indigo-100', 'bg-slate-100');
                            btn.classList.remove('text-indigo-600');
                            if(btn.classList.contains('dark:bg-indigo-900/50')) btn.classList.replace('dark:bg-indigo-900/50', 'dark:bg-slate-700');
                        }
                    }
                } catch (error) { console.error(error); }
            };

            window.deleteSong = async function(songId, btn) {
                const result = await Swal.fire({
                    title: 'Remover música?',
                    text: "A música vai desaparecer da playlist comunitária.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sim, remover',
                    cancelButtonText: 'Cancelar',
                    customClass: { popup: 'rounded-3xl' }
                });

                if (result.isConfirmed) {
                    try {
                        const response = await axios.delete(`/zona-calma/playlist/${songId}`, { headers: { 'Accept': 'application/json' } });
                        if(response.data.success) {
                            btn.closest('.flex.items-center.justify-between').remove();
                            Swal.fire({ title: 'Removida!', icon: 'success', timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-3xl' } });
                        }
                    } catch (e) {
                        Swal.fire({ title: 'Erro', text: 'Não foi possível remover a música.', icon: 'error', customClass: { popup: 'rounded-3xl' } });
                    }
                }
            };
        </script>
    </x-slot>
</x-lumina-layout>