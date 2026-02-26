<x-lumina-layout title="Zona Calma | Lumina">
    <div class="py-12 pt-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-10 text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 dark:text-white flex items-center justify-center md:justify-start gap-3">
                    <i class="ri-leaf-line text-emerald-500"></i> O Teu Santuário
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-base md:text-lg">Um espaço seguro para abrandar, respirar e voltar ao momento presente.</p>
            </div>

            {{-- Tutorial progressivo — 3 passos, persistido via localStorage --}}
            <div x-data="{
                    show: !localStorage.getItem('tour_calm_zone'),
                    step: 1,
                    total: 3,
                    dismiss() {
                        this.show = false;
                        localStorage.setItem('tour_calm_zone', '1');
                    }
                }"
                x-show="show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none;"
                class="mb-8 max-w-2xl mx-auto md:mx-0">

                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 text-white p-6 rounded-2xl shadow-xl shadow-indigo-500/20 relative">

                    {{-- Fechar --}}
                    <button @click="dismiss()" class="absolute top-4 right-4 text-white/40 hover:text-white transition-colors" title="Fechar tutorial">
                        <i class="ri-close-line text-lg"></i>
                    </button>

                    {{-- Passo 1 --}}
                    <div x-show="step === 1" x-transition.opacity.duration.300ms>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                                <i class="ri-leaf-line text-xl"></i>
                            </div>
                            <h4 class="font-bold text-lg">Bem-vindo ao teu Santuário</h4>
                        </div>
                        <p class="text-indigo-100 text-sm leading-relaxed">
                            Este espaço foi pensado para ti. Sempre que sentires a ansiedade a crescer ou precisares de um momento de pausa,
                            a Zona Calma tem ferramentas que te ajudam a abrandar e a voltar ao presente.
                        </p>
                    </div>

                    {{-- Passo 2 --}}
                    <div x-show="step === 2" x-transition.opacity.duration.300ms>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-emerald-400/30 rounded-full flex items-center justify-center shrink-0">
                                <i class="ri-focus-2-line text-xl"></i>
                            </div>
                            <h4 class="font-bold text-lg">Exercício de Grounding</h4>
                        </div>
                        <p class="text-indigo-100 text-sm leading-relaxed">
                            A técnica <strong class="text-white">5-4-3-2-1</strong> ajuda-te a sair de uma espiral de pensamentos.
                            Guia-te pelos 5 sentidos, passo a passo, para te ancorares ao momento presente.
                            Leva apenas alguns minutos.
                        </p>
                    </div>

                    {{-- Passo 3 --}}
                    <div x-show="step === 3" x-transition.opacity.duration.300ms>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-rose-400/30 rounded-full flex items-center justify-center shrink-0">
                                <i class="ri-shield-check-line text-xl"></i>
                            </div>
                            <h4 class="font-bold text-lg">Modo Crise</h4>
                        </div>
                        <p class="text-indigo-100 text-sm leading-relaxed">
                            Se estiveres num momento mais difícil, o <strong class="text-white">Modo Crise</strong> ativa um ecrã sem distrações
                            com o teu plano de segurança pessoal. É o teu porto seguro quando precisares.
                        </p>
                    </div>

                    {{-- Navegação --}}
                    <div class="flex items-center justify-between mt-5 pt-4 border-t border-white/15">
                        {{-- Indicadores de passo --}}
                        <div class="flex items-center gap-1.5">
                            <template x-for="i in total" :key="i">
                                <div class="h-1.5 rounded-full transition-all duration-300"
                                     :class="i === step ? 'w-6 bg-white' : 'w-1.5 bg-white/30'"></div>
                            </template>
                            <span class="text-[10px] text-indigo-200 ml-2" x-text="step + '/' + total"></span>
                        </div>

                        {{-- Botões --}}
                        <div class="flex items-center gap-2">
                            <button x-show="step > 1" @click="step--"
                                    class="text-indigo-200 hover:text-white text-xs font-medium transition-colors px-3 py-1.5 rounded-lg hover:bg-white/10">
                                <i class="ri-arrow-left-s-line"></i> Anterior
                            </button>
                            <button x-show="step < total" @click="step++"
                                    class="bg-white text-indigo-600 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-50 transition-colors">
                                Seguinte <i class="ri-arrow-right-s-line"></i>
                            </button>
                            <button x-show="step === total" @click="dismiss()"
                                    class="bg-white text-indigo-600 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-50 transition-colors">
                                Começar <i class="ri-check-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-12">
                <a href="{{ route('calm.grounding') }}" class="group bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-4 md:mb-6"><i class="ri-focus-2-line"></i></div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-800 dark:text-white mb-2">Grounding 5-4-3-2-1</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs md:text-sm mb-4">Técnica guiada para travar a ansiedade e voltar ao corpo.</p>
                        <span class="text-emerald-600 font-bold text-xs md:text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Iniciar Exercício <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('calm.crisis') }}" class="group bg-rose-50 dark:bg-slate-800 rounded-3xl p-6 md:p-8 border border-rose-100 dark:border-rose-900/30 shadow-sm hover:shadow-xl transition-all relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-100/50 dark:bg-rose-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-rose-200/50 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-4 md:mb-6"><i class="ri-alarm-warning-line"></i></div>
                        <h3 class="text-lg md:text-xl font-bold text-rose-900 dark:text-white mb-2">Modo Crise</h3>
                        <p class="text-rose-700/70 dark:text-slate-400 text-xs md:text-sm mb-4">Ecrã escuro, sem distrações, apenas o teu plano de segurança.</p>
                        <span class="text-rose-600 font-bold text-xs md:text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ativar Agora <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <button onclick="alert('Funcionalidade de sons a integrar!')" class="group bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all relative overflow-hidden text-left w-full">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-2xl md:text-3xl mb-4 md:mb-6"><i class="ri-rainy-line"></i></div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-800 dark:text-white mb-2">Paisagens Sonoras</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs md:text-sm mb-4">Ouve o som da chuva, lareira ou ondas em loop.</p>
                        <span class="text-blue-600 font-bold text-xs md:text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ouvir <i class="ri-play-circle-line"></i></span>
                    </div>
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-[2rem] md:rounded-[2.5rem] p-5 md:p-10 border border-slate-100 dark:border-slate-700 shadow-sm">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

                        const currentUserId = {{ Auth::id() }};
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
                        container.insertAdjacentHTML('beforeend', newHtml);
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
</x-lumina-layout>