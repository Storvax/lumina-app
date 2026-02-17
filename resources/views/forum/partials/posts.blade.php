@forelse($posts as $index => $post)
    <article class="masonry-item glass-card bg-white/80 border border-white/40 rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col relative overflow-visible group {{ $post->is_sensitive ? 'sensitive-container' : '' }} {{ $post->is_pinned ? 'ring-2 ring-indigo-500/30 bg-indigo-50/50' : '' }}" 
             id="post-card-{{ $post->id }}">
             
        @if($post->is_pinned)
            <div class="absolute -top-3 -left-3 z-30 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg transform -rotate-12">
                <i class="ri-pushpin-fill"></i>
            </div>
        @endif

        <a href="{{ route('forum.show', $post) }}" class="absolute inset-0 z-0"></a>

        @if($post->is_sensitive)
            <div class="overlay-warning absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/80 backdrop-blur-md p-6 text-center cursor-pointer transition-opacity duration-300" onclick="event.preventDefault(); this.parentElement.classList.add('revealed')">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center mb-3 shadow-inner"><i class="ri-eye-close-line text-xl"></i></div>
                <p class="font-bold text-slate-800 text-sm">Conteúdo Sensível</p>
                <button class="text-[10px] font-bold text-indigo-600 border border-indigo-200 bg-white px-4 py-1.5 rounded-full hover:bg-indigo-50 mt-3 shadow-sm">Mostrar</button>
            </div>
        @endif

        <div class="{{ $post->is_sensitive ? 'blur-content' : '' }} flex flex-col h-full relative z-10 pointer-events-none">
            
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-100 to-white border border-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shadow-sm">
                        {{ substr($post->user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700 truncate max-w-[100px] md:max-w-[120px]">{{ $post->user->name }}</p>
                        <p class="text-[10px] text-slate-400 font-medium">{{ $post->created_at->diffForHumans(null, true) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 pointer-events-auto relative">
                    
                    @php
                        $colors = [
                            'hope' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            'vent' => 'bg-rose-50 text-rose-600 border-rose-100',
                            'anxiety' => 'bg-amber-50 text-amber-600 border-amber-100'
                        ];
                        $labels = ['hope' => 'Esperança', 'vent' => 'Desabafo', 'anxiety' => 'Ansiedade'];
                        $colorClass = $colors[$post->tag] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                        $labelText = $labels[$post->tag] ?? 'Geral';
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide border {{ $colorClass }}">
                        {{ $labelText }}
                    </span>

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition-colors z-30 relative">
                                <i class="ri-more-2-fill"></i>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden py-1"
                                 style="display: none;">
                                
                                @if(Auth::user()->isModerator())
                                    <form action="{{ route('forum.pin', $post) }}" method="POST"> @csrf @method('PATCH')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2">
                                            <i class="ri-pushpin-line"></i> {{ $post->is_pinned ? 'Desafixar' : 'Fixar no Topo' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('forum.lock', $post) }}" method="POST"> @csrf @method('PATCH')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2">
                                            <i class="{{ $post->is_locked ? 'ri-lock-unlock-line' : 'ri-lock-line' }}"></i> {{ $post->is_locked ? 'Destrancar' : 'Trancar' }}
                                        </button>
                                    </form>
                                    
                                    @if(!$post->user->isShadowbanned() && !$post->user->isModerator())
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        <button onclick="shadowbanUser({{ $post->user->id }}, '{{ $post->user->name }}')" 
                                                class="w-full text-left px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50 flex items-center gap-2">
                                            <i class="ri-ghost-line"></i> Shadowban User
                                        </button>
                                    @endif

                                    <div class="h-px bg-slate-100 my-1"></div>
                                @endif

                                @if(Auth::id() === $post->user_id)
                                    <button onclick="openEditModal({{ $post->id }}, '{{ e($post->title) }}', '{{ e($post->content) }}', '{{ $post->tag }}', {{ $post->is_sensitive ? 1 : 0 }})" 
                                            class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2">
                                        <i class="ri-pencil-line"></i> Editar
                                    </button>
                                @endif

                                @if(Auth::user()->isModerator() || Auth::id() === $post->user_id)
                                    <button onclick="openDeleteModal({{ $post->id }})" class="w-full text-left px-4 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2">
                                        <i class="ri-delete-bin-line"></i> Eliminar
                                    </button>
                                @endif

                                @if(Auth::id() !== $post->user_id && !Auth::user()->isModerator())
                                    <button onclick="openReportModal({{ $post->id }})" 
                                            class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2">
                                        <i class="ri-flag-line"></i> Denunciar
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
                    <p class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-1.5 rounded-lg w-full text-center"><i class="ri-lock-line"></i> Comentários desativados</p>
                @else
                    <div class="flex gap-1.5">
                        <button onclick="event.preventDefault(); react({{ $post->id }}, 'hug', this)" 
                                class="group/btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 hover:bg-rose-50 border border-slate-100 hover:border-rose-100 transition-all" title="Enviar Abraço">
                            <span class="text-base group-hover/btn:scale-125 transition-transform">🫂</span> 
                            <span class="count-hug text-xs font-bold text-slate-500 group-hover/btn:text-rose-600">{{ $post->reactions->where('type', 'hug')->count() }}</span>
                        </button>
                        
                        <button onclick="event.preventDefault(); react({{ $post->id }}, 'candle', this)" 
                                class="group/btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 hover:bg-amber-50 border border-slate-100 hover:border-amber-100 transition-all" title="Acender Vela">
                            <span class="text-base group-hover/btn:scale-125 transition-transform">🕯️</span> 
                            <span class="count-candle text-xs font-bold text-slate-500 group-hover/btn:text-amber-600">{{ $post->reactions->where('type', 'candle')->count() }}</span>
                        </button>
                        
                        <button onclick="event.preventDefault(); react({{ $post->id }}, 'ear', this)" 
                                class="group/btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition-all" title="Estou a ouvir">
                            <span class="text-base group-hover/btn:scale-125 transition-transform">👂</span> 
                            <span class="count-ear text-xs font-bold text-slate-500 group-hover/btn:text-indigo-600">{{ $post->reactions->where('type', 'ear')->count() }}</span>
                        </button>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="flex items-center gap-1.5 text-slate-400 text-xs font-medium bg-white px-2 py-1 rounded-md shadow-sm border border-slate-100">
                            <i class="ri-chat-1-line"></i> {{ $post->comments->count() }}
                        </span>

                        @auth
                            <button onclick="toggleSave({{ $post->id }}, this)" 
                                    class="text-slate-400 hover:text-indigo-600 transition-colors {{ Auth::user()->savedPosts->contains($post->id) ? 'text-indigo-600' : '' }}" 
                                    title="Guardar para ler mais tarde">
                                <i class="{{ Auth::user()->savedPosts->contains($post->id) ? 'ri-bookmark-fill' : 'ri-bookmark-line' }} text-lg"></i>
                            </button>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </article>
@empty
    <div class="col-span-full text-center py-20 opacity-60">
        <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400"><i class="ri-seedling-line text-4xl"></i></div>
        <h3 class="text-lg font-bold text-slate-600">Ainda está tudo calmo.</h3>
        <p class="text-slate-400">Sê o primeiro a partilhar algo.</p>
    </div>
@endforelse

<div class="masonry-item col-span-full mt-8 flex justify-center w-full">
    {{ $posts->links('pagination::tailwind') }}
</div>