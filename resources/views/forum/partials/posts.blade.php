@forelse($posts as $index => $post)
    @php
        $isSaved = Auth::check() && Auth::user()->savedPosts->contains($post->id) ? 'true' : 'false';
    @endphp
    
    <article class="masonry-item relative group mb-6" id="post-wrapper-{{ $post->id }}" aria-labelledby="post-title-{{ $post->id }}"
             @auth
             x-data="{
                 startX: 0,
                 currentX: 0,
                 swiping: false,
                 saved: {{ $isSaved }},
                 touchStart(e) { this.startX = e.touches[0].clientX; this.swiping = true; },
                 touchMove(e) {
                     if (!this.swiping) return;
                     let diff = e.touches[0].clientX - this.startX;
                     if (diff < 0 && diff >= -120) { this.currentX = diff; }
                 },
                 touchEnd() {
                     this.swiping = false;
                     if (this.currentX <= -75) { this.savePost(); }
                     this.currentX = 0; 
                 },
                 async savePost() {
                     try {
                         const res = await axios.post(`/mural/{{ $post->id }}/save`);
                         this.saved = res.data.saved;
                         if(window.navigator && window.navigator.vibrate) navigator.vibrate(40);
                     } catch(e) {}
                 }
             }"
             @touchstart.passive="touchStart"
             @touchmove.passive="touchMove"
             @touchend.passive="touchEnd"
             @endauth>
             
        @if($post->is_pinned)
            <div class="absolute -top-3 -left-3 z-30 w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg transform -rotate-12" aria-label="{{ __('Publicação Fixada') }}" title="{{ __('Publicação Fixada') }}">
                <i class="ri-pushpin-fill" aria-hidden="true"></i>
            </div>
        @endif

        @auth
        <div class="absolute inset-0 bg-indigo-500 rounded-3xl flex items-center justify-end pr-8 z-0 shadow-inner overflow-hidden" aria-hidden="true">
            <div class="flex flex-col items-center text-white transition-all duration-200" :class="currentX <= -75 ? 'scale-110 font-bold opacity-100' : 'scale-90 opacity-50'">
                <i class="text-3xl transition-colors" :class="saved ? 'ri-bookmark-fill text-indigo-200' : 'ri-bookmark-line'"></i>
                <span class="text-[9px] uppercase tracking-widest mt-1" x-text="saved ? 'Remover' : 'Guardar'"></span>
            </div>
        </div>
        @endauth

        <div class="glass-card bg-white/80 border border-white/40 rounded-3xl p-6 hover:shadow-xl flex flex-col h-full relative z-10 {{ $post->is_sensitive ? 'sensitive-container' : '' }} {{ $post->is_pinned ? 'ring-2 ring-indigo-500/30 bg-indigo-50/50' : '' }}"
             @auth
             :style="swiping ? `transform: translateX(${currentX}px); transition: none;` : `transform: translateX(0px); transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);`"
             @endauth>

            <a href="{{ route('forum.show', $post) }}" class="absolute inset-0 z-0 focus-visible:ring-4 focus-visible:ring-indigo-500 rounded-3xl outline-none" aria-label="{{ __('Ler publicação completa:') }} {{ $post->title }}"></a>

            {{-- O NOVO BOTÃO DE REVELAR: Gigante, acessível e semântico --}}
            @if($post->is_sensitive)
                <button type="button" 
                     aria-label="{{ __('Mostrar Conteúdo Sensível') }}"
                     onclick="event.preventDefault(); this.parentElement.classList.add('revealed'); setTimeout(() => this.style.display = 'none', 300);"
                     class="overlay-warning absolute inset-0 z-[40] w-full h-full flex flex-col items-center justify-center bg-white/70 backdrop-blur-md p-6 text-center cursor-pointer transition-all duration-300 rounded-3xl focus-visible:ring-4 focus-visible:ring-rose-500 outline-none group/sensitive">
                    <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center mb-4 shadow-sm group-hover/sensitive:scale-110 transition-transform"><i class="ri-eye-close-line text-2xl" aria-hidden="true"></i></div>
                    <p class="font-bold text-slate-800 text-base mb-1">{{ __('Conteúdo Sensível') }}</p>
                    <span class="text-xs font-bold text-indigo-600 bg-white border border-indigo-100 px-5 py-2.5 rounded-full mt-2 shadow-sm group-hover/sensitive:bg-indigo-50 transition-colors flex items-center gap-2">
                        {{ __('Toca para revelar') }} <i class="ri-lock-unlock-line"></i>
                    </span>
                </button>
            @endif

            <div class="{{ $post->is_sensitive ? 'blur-content' : '' }} flex flex-col h-full relative z-10 pointer-events-none">
                
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-100 to-white border border-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shadow-sm" aria-hidden="true">
                            {{ substr($post->user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700 truncate max-w-[100px] md:max-w-[120px]">{{ $post->user->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $post->created_at->diffForHumans(null, true) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pointer-events-auto relative">
                        @php
                            $colors = ['hope' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'vent' => 'bg-rose-50 text-rose-600 border-rose-100', 'anxiety' => 'bg-amber-50 text-amber-600 border-amber-100'];
                            $labels = ['hope' => 'Esperança', 'vent' => 'Desabafo', 'anxiety' => 'Ansiedade'];
                        @endphp
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide border {{ $colors[$post->tag] ?? 'bg-slate-50' }}">
                            {{ __($labels[$post->tag] ?? 'Geral') }}
                        </span>

                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        @click.outside="open = false" 
                                        @keydown.escape.window="open = false"
                                        class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition-colors z-30 relative focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none"
                                        aria-label="{{ __('Opções da publicação') }}" 
                                        aria-haspopup="true" 
                                        :aria-expanded="open">
                                    <i class="ri-more-2-fill text-lg" aria-hidden="true"></i>
                                </button>

                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden py-1"
                                     style="display: none;"
                                     role="menu">
                                    
                                    @if(Auth::user()->isModerator())
                                        <form action="{{ route('forum.pin', $post) }}" method="POST"> @csrf @method('PATCH')
                                            <button type="submit" class="w-full text-left px-4 py-3 min-h-[44px] text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2 focus-visible:bg-indigo-50 outline-none" role="menuitem">
                                                <i class="ri-pushpin-line" aria-hidden="true"></i> {{ $post->is_pinned ? __('Desafixar') : __('Fixar no Topo') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('forum.lock', $post) }}" method="POST"> @csrf @method('PATCH')
                                            <button type="submit" class="w-full text-left px-4 py-3 min-h-[44px] text-sm font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2 focus-visible:bg-amber-50 outline-none" role="menuitem">
                                                <i class="{{ $post->is_locked ? 'ri-lock-unlock-line' : 'ri-lock-line' }}" aria-hidden="true"></i> {{ $post->is_locked ? __('Destrancar') : __('Trancar') }}
                                            </button>
                                        </form>
                                        <div class="h-px bg-slate-100 my-1"></div>
                                    @endif

                                    @if(Auth::user()->isModerator() || Auth::id() === $post->user_id)
                                        <button onclick="openDeleteModal({{ $post->id }})" class="w-full text-left px-4 py-3 min-h-[44px] text-sm font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2 focus-visible:bg-rose-50 outline-none" role="menuitem">
                                            <i class="ri-delete-bin-line" aria-hidden="true"></i> {{ __('Eliminar') }}
                                        </button>
                                    @endif

                                    @if(Auth::id() !== $post->user_id && !Auth::user()->isModerator())
                                        <button onclick="openReportModal({{ $post->id }})" class="w-full text-left px-4 py-3 min-h-[44px] text-sm font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2 focus-visible:bg-amber-50 outline-none" role="menuitem">
                                            <i class="ri-flag-line" aria-hidden="true"></i> {{ __('Denunciar') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>

                <h3 id="post-title-{{ $post->id }}" class="text-lg font-bold text-slate-800 mb-2 leading-tight group-hover:text-indigo-600 transition-colors flex items-center gap-2">
                    @if($post->is_locked) <i class="ri-lock-fill text-amber-500 text-sm" aria-label="{{ __('Comentários bloqueados') }}"></i> @endif
                    {{ $post->title }}
                </h3>
                
                {{-- MURAL SUSSURRADO (Leitor de Áudio Bónus) --}}
                @if(isset($post->audio_path) && $post->audio_path)
                    <div class="mb-4 mt-2 bg-slate-50 border border-slate-100 rounded-2xl p-2 pointer-events-auto">
                        <audio src="{{ asset('storage/' . $post->audio_path) }}" controls class="w-full h-10" preload="none"></audio>
                    </div>
                @endif

                {{-- LÓGICA DE RESUMO IA COM ALPINE JS --}}
                <div x-data="{ 
                        viewMode: 'original', 
                        summary: null,
                        async fetchSummary() {
                            this.viewMode = 'loading';
                            try {
                                const response = await axios.post(`/mural/{{ $post->id }}/summarize`);
                                this.summary = response.data.summary;
                                this.viewMode = 'summary';
                            } catch (error) {
                                this.viewMode = 'original';
                                window.showAlert('Ops', 'Não foi possível resumir o texto agora. Tenta mais tarde.', 'error');
                            }
                        }
                    }">
                    
                    {{-- TEXTO ORIGINAL --}}
                    <div x-show="viewMode === 'original'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" class="relative">
                        <div class="text-slate-600 text-[15px] leading-relaxed mb-6 opacity-90 line-clamp-6">
                            {{ Str::limit($post->content, 250) }}
                        </div>
                        
                        @if(strlen($post->content) > 200)
                            <div class="absolute bottom-[-15px] left-0 w-full h-12 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                            <button @click.prevent="fetchSummary()" 
                                    class="relative -mt-2 mb-4 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-bold flex items-center gap-1.5 hover:bg-indigo-100 transition-colors pointer-events-auto min-h-[44px]">
                                <i class="ri-sparkling-fill text-indigo-400"></i> Simplificar Leitura
                            </button>
                        @endif
                    </div>

                    {{-- ESTADO DE CARREGAMENTO (LOADING) --}}
                    <div x-show="viewMode === 'loading'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="ri-loader-4-line animate-spin text-indigo-500 text-lg"></i>
                            <span class="text-sm font-bold text-slate-700">A ler com atenção...</span>
                        </div>
                    </div>

                    {{-- TEXTO RESUMIDO --}}
                    <div x-show="viewMode === 'summary'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" class="mb-6 relative pointer-events-auto">
                        <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100/50">
                            <div class="flex items-center justify-between mb-3 border-b border-indigo-100 pb-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-400 flex items-center gap-1"><i class="ri-sparkling-fill"></i> Pontos Principais</span>
                                <button @click.prevent="viewMode = 'original'" class="text-[10px] font-bold text-slate-400 hover:text-slate-600 transition-colors min-h-[44px] px-2 -mr-2 -my-2">Ler original</button>
                            </div>
                            <div class="text-sm text-slate-700 leading-relaxed space-y-2 prose prose-sm" x-html="summary"></div>
                        </div>
                    </div>

                </div>

                {{-- AÇÕES (Tamanho Tátil Melhorado) --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto pointer-events-auto">
                    @if($post->is_locked)
                        <p class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-3 rounded-lg w-full text-center flex items-center justify-center gap-2"><i class="ri-lock-line"></i> {{ __('Comentários desativados') }}</p>
                    @else
                        <div class="flex gap-2">
                            <button onclick="event.preventDefault(); react({{ $post->id }}, 'hug', this)" 
                                    class="group/btn flex items-center justify-center gap-1.5 px-3 py-2 min-h-[44px] min-w-[50px] rounded-xl bg-slate-50 hover:bg-rose-50 border border-slate-100 hover:border-rose-100 transition-all focus-visible:ring-2 focus-visible:ring-rose-500 outline-none" 
                                    aria-label="{{ __('Enviar abraço. Total atual:') }} {{ $post->reactions->where('type', 'hug')->count() }}">
                                <span class="text-base group-hover/btn:scale-125 transition-transform" aria-hidden="true">🫂</span> 
                                <span class="count-hug text-sm font-bold text-slate-500 group-hover/btn:text-rose-600">{{ $post->reactions->where('type', 'hug')->count() }}</span>
                            </button>
                            
                            <button onclick="event.preventDefault(); react({{ $post->id }}, 'candle', this)" 
                                    class="group/btn flex items-center justify-center gap-1.5 px-3 py-2 min-h-[44px] min-w-[50px] rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-100 hover:border-amber-100 transition-all focus-visible:ring-2 focus-visible:ring-amber-500 outline-none" 
                                    aria-label="{{ __('Acender vela. Total atual:') }} {{ $post->reactions->where('type', 'candle')->count() }}">
                                <span class="text-base group-hover/btn:scale-125 transition-transform" aria-hidden="true">🕯️</span> 
                                <span class="count-candle text-sm font-bold text-slate-500 group-hover/btn:text-amber-600">{{ $post->reactions->where('type', 'candle')->count() }}</span>
                            </button>
                            
                            <button onclick="event.preventDefault(); react({{ $post->id }}, 'ear', this)" 
                                    class="group/btn flex items-center justify-center gap-1.5 px-3 py-2 min-h-[44px] min-w-[50px] rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition-all focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none" 
                                    aria-label="{{ __('Oferecer ouvidos. Total atual:') }} {{ $post->reactions->where('type', 'ear')->count() }}">
                                <span class="text-base group-hover/btn:scale-125 transition-transform" aria-hidden="true">👂</span> 
                                <span class="count-ear text-sm font-bold text-slate-500 group-hover/btn:text-indigo-600">{{ $post->reactions->where('type', 'ear')->count() }}</span>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1.5 text-slate-400 text-sm font-bold bg-white px-3 py-2 min-h-[44px] rounded-xl shadow-sm border border-slate-100" aria-label="{{ $post->comments->count() }} {{ __('comentários') }}">
                                <i class="ri-chat-1-line text-lg" aria-hidden="true"></i> {{ $post->comments->count() }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </article>
@empty
    <div class="py-20 text-center" style="column-span: all;">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300" aria-hidden="true">
            <i class="ri-leaf-line text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">{{ __('Sem histórias por agora') }}</h3>
        <p class="text-slate-500 mb-6">{{ __('Sê a primeira pessoa a partilhar e a acender uma luz.') }}</p>
        @auth
            <button onclick="togglePostModal()" class="inline-flex items-center gap-2 bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-3 rounded-full text-sm font-bold transition-all active:scale-95 focus-visible:ring-4 focus-visible:ring-indigo-500 min-h-[44px]">
                <i class="ri-quill-pen-line" aria-hidden="true"></i> {{ __('Escrever a primeira história') }}
            </button>
        @endauth
    </div>
@endforelse