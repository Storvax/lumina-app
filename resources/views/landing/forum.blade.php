<section id="forum" class="py-24 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4">
            <div class="scroll-reveal">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Mural da Esperança</h2>
                <p class="text-slate-500 dark:text-slate-400 max-w-xl">Discussões assíncronas. Deixa o teu pensamento, volta mais tarde para ver o apoio que recebeste.</p>
            </div>
            <a href="{{ route('forum.index') }}" class="px-6 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-bold hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors"><i class="ri-add-line mr-1"></i> Criar Tópico</a>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                
                @forelse($recentPosts as $post)
                <div class="scroll-reveal group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-primary-100 dark:hover:border-primary-700/50 transition-all cursor-pointer relative" onclick="window.location='{{ route('forum.show', $post) }}'">
                    <div class="flex items-start justify-between">
                        <div class="flex gap-4">
                            @if($post->user->avatar)
                                <img src="{{ asset('storage/' . $post->user->avatar) }}" class="w-10 h-10 shrink-0 rounded-full object-cover border border-slate-100 dark:border-slate-600">
                            @else
                                <div class="w-10 h-10 shrink-0 rounded-full bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-800">
                                    {{ substr($post->user->name, 0, 1) }}
                                </div>
                            @endif
                            
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 uppercase tracking-wide">Discussão</span>
                                    <span class="text-xs text-slate-400">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                                <h4 class="font-bold text-slate-800 dark:text-slate-200 text-lg group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate pr-4">{{ $post->title }}</h4>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 line-clamp-2 leading-relaxed">{{ Str::limit($post->content, 140) }}</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-end gap-2 text-slate-400 shrink-0 pl-2">
                            <div class="flex items-center gap-1" title="Reações">
                                <i class="ri-heart-3-line text-lg group-hover:text-rose-500 transition-colors"></i>
                                <span class="text-xs font-bold">{{ $post->reactions_count }}</span>
                            </div>
                            <div class="flex items-center gap-1" title="Comentários">
                                <i class="ri-chat-1-line text-lg group-hover:text-blue-500 transition-colors"></i>
                                <span class="text-xs font-bold">{{ $post->comments_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="p-12 text-center bg-slate-50 dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700">
                        <div class="w-16 h-16 bg-white dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 dark:text-slate-500 shadow-sm"><i class="ri-discuss-line text-3xl"></i></div>
                        <h3 class="font-bold text-slate-800 dark:text-white">O mural está silencioso</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Sê o primeiro a partilhar uma história de esperança hoje.</p>
                        <a href="{{ route('forum.index') }}" class="text-primary-600 dark:text-primary-400 font-bold hover:underline">Iniciar conversa</a>
                    </div>
                @endforelse

                <a href="{{ route('forum.index') }}" class="block w-full py-4 text-center text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-700">Ver todos os tópicos</a>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-6 h-fit border border-slate-100 dark:border-slate-800">
                <h4 class="font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2 text-sm uppercase tracking-wider"><i class="ri-hashtag text-primary-500"></i> Temas Ativos</h4>
                <div class="flex flex-wrap gap-2">
                    <a href="#" class="px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-500 hover:text-primary-600 transition-all shadow-sm">Depressão</a>
                    <a href="#" class="px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-500 hover:text-primary-600 transition-all shadow-sm">Ansiedade</a>
                    <a href="#" class="px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-500 hover:text-primary-600 transition-all shadow-sm">Work-Life</a>
                    <a href="#" class="px-3 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-500 hover:text-primary-600 transition-all shadow-sm">Luto</a>
                </div>
            </div>
        </div>
    </div>
</section>