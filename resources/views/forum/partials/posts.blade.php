@forelse($posts as $index => $post)
    @php
        $isSaved = Auth::check() && Auth::user()->savedPosts->contains($post->id) ? 'true' : 'false';
    @endphp
    
    <article class="masonry-item relative group mb-6" id="post-wrapper-{{ $post->id }}"
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
                     // Apenas permite swipe para a esquerda (guardar) com um limite de 120px
                     if (diff < 0 && diff >= -120) { this.currentX = diff; }
                 },
                 touchEnd() {
                     this.swiping = false;
                     // Se deslizou mais de 75px, ativa a ação!
                     if (this.currentX <= -75) { this.savePost(); }
                     this.currentX = 0; // O cartão volta com uma transição elástica
                 },
                 async savePost() {
                     try {
                         const res = await axios.post(`/mural/{{ $post->id }}/save`);
                         this.saved = res.data.saved;
                         // Magia: Feedback físico (Vibração) no telemóvel
                         if(window.navigator && window.navigator.vibrate) navigator.vibrate(40);
                     } catch(e) {}
                 }
             }"
             @touchstart.passive="touchStart"
             @touchmove.passive="touchMove"
             @touchend.passive="touchEnd"
             @endauth>
             
        @if($post->is_pinned)
            <div class="absolute -top-3 -left-3 z-30 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg transform -rotate-12" aria-label="Post Fixado" title="Post Fixado">
                <i class="ri-pushpin-fill" aria-hidden="true"></i>
            </div>
        @endif

        @auth
        <div class="absolute inset-0 bg-indigo-500 rounded-3xl flex items-center justify-end pr-8 z-0 shadow-inner overflow-hidden">
            <div class="flex flex-col items-center text-white transition-all duration-200" 
                 :class="currentX <= -75 ? 'scale-110 font-bold opacity-100' : 'scale-90 opacity-50'">
                <i class="text-3xl transition-colors" :class="saved ? 'ri-bookmark-fill text-indigo-200' : 'ri-bookmark-line'"></i>
                <span class="text-[9px] uppercase tracking-widest mt-1" x-text="saved ? 'Remover' : 'Guardar'"></span>
            </div>
        </div>
        @endauth

        <div class="glass-card bg-white/80 border border-white/40 rounded-3xl p-6 hover:shadow-xl flex flex-col h-full relative z-10 {{ $post->is_sensitive ? 'sensitive-container' : '' }} {{ $post->is_pinned ? 'ring-2 ring-indigo-500/30 bg-indigo-50/50' : '' }}"
             @auth
             :style="swiping ? `transform: translateX(${currentX}px); transition: none;` : `transform: translateX(0px); transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);`"
             @endauth>

            <a href="{{ route('forum.show', $post) }}" class="absolute inset-0 z-0" aria-label="Ver post completo: {{ $post->title }}"></a>

            @if($post->is_sensitive)
                <div class="overlay-warning absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/80 backdrop-blur-md p-6 text-center cursor-pointer transition-opacity duration-300 rounded-3xl" onclick="event.preventDefault(); this.parentElement.classList.add('revealed'); setTimeout(() => this.style.display = 'none', 300);">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center mb-3 shadow-inner"><i class="ri-eye-close-line text-xl" aria-hidden="true"></i></div>
                    <p class="font-bold text-slate-800 text-sm">Conteúdo Sensível</p>
                    <button class="text-[10px] font-bold text-indigo-600 border border-indigo-200 bg-white px-4 py-1.5 rounded-full hover:bg-indigo-50 mt-3 shadow-sm">Mostrar Conteúdo</button>
                </div>
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
                            {{ $labels[$post->tag] ?? 'Geral' }}
                        </span>

                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.outside="open = false" 
                                        class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition-colors z-30 relative"
                                        aria-label="Opções do post" 
                                        aria-haspopup="true" 
                                        :aria-expanded="open">
                                    <i class="ri-more-2-fill" aria-hidden="true"></i>
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
                                            <button type="submit" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2" role="menuitem">
                                                <i class="ri-pushpin-line" aria-hidden="true"></i> {{ $post->is_pinned ? 'Desafixar' : 'Fixar no Topo' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('forum.lock', $post) }}" method="POST"> @csrf @method('PATCH')
                                            <button type="submit" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2" role="menuitem">
                                                <i class="{{ $post->is_locked ? 'ri-lock-unlock-line' : 'ri-lock-line' }}" aria-hidden="true"></i> {{ $post->is_locked ? 'Destrancar' : 'Trancar' }}
                                            </button>
                                        </form>
                                        @if(!$post->user->isShadowbanned() && !$post->user->isModerator())
                                            <div class="h-px bg-slate-100 my-1"></div>
                                            <button onclick="shadowbanUser({{ $post->user->id }}, '{{ $post->user->name }}')" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50 flex items-center gap-2" role="menuitem">
                                                <i class="ri-ghost-line" aria-hidden="true"></i> Shadowban User
                                            </button>
                                        @endif
                                        <div class="h-px bg-slate-100 my-1"></div>
                                    @endif

                                    @if(Auth::id() === $post->user_id)
                                        <button onclick="openEditModal({{ $post->id }}, '{{ e($post->title) }}', '{{ e($post->content) }}', '{{ $post->tag }}', {{ $post->is_sensitive ? 1 : 0 }})" 
                                                class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2" role="menuitem">
                                            <i class="ri-pencil-line" aria-hidden="true"></i> Editar
                                        </button>
                                    @endif

                                    @if(Auth::user()->isModerator() || Auth::id() === $post->user_id)
                                        <button onclick="openDeleteModal({{ $post->id }})" class="w-full text-left px-4 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2" role="menuitem">
                                            <i class="ri-delete-bin-line" aria-hidden="true"></i> Eliminar
                                        </button>
                                    @endif

                                    @if(Auth::id() !== $post->user_id && !Auth::user()->isModerator())
                                        <button onclick="openReportModal({{ $post->id }})" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2" role="menuitem">
                                            <i class="ri-flag-line" aria-hidden="true"></i> Denunciar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>

                <h3 class="text-lg font-bold text-slate-800 mb-2 leading-tight group-hover:text-indigo-600 transition-colors flex items-center gap-2">
                    @if($post->is_locked) <i class="ri-lock-fill text-amber-500 text-sm" title="Comentários fechados"></i> @endif
                    {{ $post->title }}
                </h3>
                <div class="text-slate-600 text-[15px] leading-relaxed mb-6 opacity-90 line-clamp-6">
                    {{ Str::limit($post->content, 250) }}
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto pointer-events-auto">
                    @if($post->is_locked)
                        <p class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-1.5 rounded-lg w-full text-center"><i class="ri-lock-line" aria-hidden="true"></i> Comentários desativados</p>
                    @else
                        <div class="flex gap-1.5">
                            <button onclick="event.preventDefault(); react({{ $post->id }}, 'hug', this)" 
                                    class="group/btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 hover:bg-rose-50 border border-slate-100 hover:border-rose-100 transition-all" 
                                    title="Enviar Abraço" aria-label="Enviar abraço">
                                <span class="text-base group-hover/btn:scale-125 transition-transform" aria-hidden="true">🫂</span> 
                                <span class="count-hug text-xs font-bold text-slate-500 group-hover/btn:text-rose-600">{{ $post->reactions->where('type', 'hug')->count() }}</span>
                            </button>
                            
                            <button onclick="event.preventDefault(); react({{ $post->id }}, 'candle', this)" 
                                    class="group/btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 hover:bg-amber-50 border border-slate-100 hover:border-amber-100 transition-all" 
                                    title="Acender Vela" aria-label="Acender vela">
                                <span class="text-base group-hover/btn:scale-125 transition-transform" aria-hidden="true">🕯️</span> 
                                <span class="count-candle text-xs font-bold text-slate-500 group-hover/btn:text-amber-600">{{ $post->reactions->where('type', 'candle')->count() }}</span>
                            </button>
                            
                            <button onclick="event.preventDefault(); react({{ $post->id }}, 'ear', this)" 
                                    class="group/btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition-all" 
                                    title="Estou a ouvir" aria-label="Estou a ouvir">
                                <span class="text-base group-hover/btn:scale-125 transition-transform" aria-hidden="true">👂</span> 
                                <span class="count-ear text-xs font-bold text-slate-500 group-hover/btn:text-indigo-600">{{ $post->reactions->where('type', 'ear')->count() }}</span>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1.5 text-slate-400 text-xs font-medium bg-white px-2 py-1 rounded-md shadow-sm border border-slate-100" title="{{ $post->comments->count() }} comentários">
                                <i class="ri-chat-1-line" aria-hidden="true"></i> {{ $post->comments->count() }}
                            </span>

                            @auth
                                <button @click.prevent="savePost()" 
                                        class="text-slate-400 hover:text-indigo-600 transition-colors"
                                        :class="saved ? 'text-indigo-600' : ''"
                                        title="Guardar para ler mais tarde" aria-label="Guardar post">
                                    <i :class="saved ? 'ri-bookmark-fill' : 'ri-bookmark-line'" class="text-lg" aria-hidden="true"></i>
                                </button>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </article>
@empty
    <div class="col-span-full py-20 text-center">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
            <i class="ri-leaf-line text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Sem histórias por agora</h3>
        <p class="text-slate-500">Sê a primeira pessoa a partilhar e a acender uma luz.</p>
    </div>
@endforelse