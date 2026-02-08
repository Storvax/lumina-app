@forelse($posts as $index => $post)
    <article class="glass-card rounded-2xl p-6 hover:shadow-xl transition-all duration-300 flex flex-col h-full group animate-fade-up relative overflow-hidden {{ $post->is_sensitive ? 'sensitive-container' : '' }}" 
             id="post-card-{{ $post->id }}"
             style="animation-delay: {{ ($index * 0.05) }}s;"> <a href="{{ route('forum.show', $post) }}" class="absolute inset-0 z-0"></a>

        @if($post->is_sensitive)
            <div class="overlay-warning absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/70 backdrop-blur-sm p-6 text-center cursor-pointer transition-opacity duration-300" onclick="event.preventDefault(); this.parentElement.classList.add('revealed')">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center mb-3 shadow-sm"><i class="ri-eye-close-line text-xl"></i></div>
                <p class="font-bold text-slate-800 text-sm">Conteúdo Sensível</p>
                <button class="text-[10px] font-bold text-indigo-600 border border-indigo-200 bg-white px-3 py-1 rounded-full hover:bg-indigo-50 mt-2">Ver conteúdo</button>
            </div>
        @endif

        <div class="{{ $post->is_sensitive ? 'blur-content' : '' }} flex flex-col h-full relative z-10 pointer-events-none">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm uppercase">
                    {{ substr($post->user->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-700 truncate max-w-[150px]">{{ $post->user->name }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $post->created_at->diffForHumans(null, true) }} • 
                        @if($post->tag == 'hope') <span class="text-emerald-600 font-medium">#Esperança</span>
                        @elseif($post->tag == 'vent') <span class="text-rose-500 font-medium">#Desabafo</span>
                        @else <span class="text-amber-600 font-medium">#Ansiedade</span>
                        @endif
                    </p>
                </div>
            </div>

            <h3 class="text-lg font-bold text-slate-800 mb-2 leading-tight">{{ $post->title }}</h3>
            <div class="text-slate-600 text-sm leading-relaxed mb-6 flex-1 opacity-90 line-clamp-4">
                {{ Str::limit($post->content, 180) }}
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto pointer-events-auto">
                <div class="flex gap-1">
                    <button onclick="event.preventDefault(); react({{ $post->id }}, 'hug', this)" 
                            class="flex items-center gap-1 px-2 py-1 rounded-full hover:bg-rose-50 text-xs font-bold text-slate-400 hover:text-rose-500 transition-all" title="Enviar Abraço">
                        <span class="text-base">🫂</span> <span class="count-hug">{{ $post->reactions->where('type', 'hug')->count() }}</span>
                    </button>
                    
                    <button onclick="event.preventDefault(); react({{ $post->id }}, 'candle', this)" 
                            class="flex items-center gap-1 px-2 py-1 rounded-full hover:bg-amber-50 text-xs font-bold text-slate-400 hover:text-amber-500 transition-all" title="Acender Vela">
                        <span class="text-base">🕯️</span> <span class="count-candle">{{ $post->reactions->where('type', 'candle')->count() }}</span>
                    </button>
                    
                    <button onclick="event.preventDefault(); react({{ $post->id }}, 'ear', this)" 
                            class="flex items-center gap-1 px-2 py-1 rounded-full hover:bg-indigo-50 text-xs font-bold text-slate-400 hover:text-indigo-500 transition-all" title="Estou a ouvir">
                        <span class="text-base">👂</span> <span class="count-ear">{{ $post->reactions->where('type', 'ear')->count() }}</span>
                    </button>
                </div>
                
                <span class="flex items-center gap-1 text-slate-400 text-xs font-medium">
                    <i class="ri-chat-1-line"></i> {{ $post->comments->count() }}
                </span>
            </div>
        </div>
    </article>
@empty
    <div class="col-span-full text-center py-20 opacity-60">
        <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400"><i class="ri-seedling-line text-4xl"></i></div>
        <h3 class="text-lg font-bold text-slate-600">Nada encontrado.</h3>
        <p class="text-slate-400">Não há posts com esta categoria.</p>
    </div>
@endforelse

<div class="col-span-full mt-8 flex justify-center">
    {{ $posts->links('pagination::tailwind') }}
</div>