<x-lumina-layout title="Biblioteca | Lumina">
    <div class="py-12 pt-32">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <x-emotional-breadcrumb :items="[['label' => 'Biblioteca']]" />

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 animate-fade-up">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white">Biblioteca</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Recursos, livros e ferramentas aprovadas pela comunidade.</p>
                </div>
                
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar flex-1">
                        <a href="{{ route('library.index') }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors whitespace-nowrap {{ !request('type') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Todos</a>
                        <a href="{{ route('library.index', ['type' => 'book']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors whitespace-nowrap {{ request('type') == 'book' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Livros</a>
                        <a href="{{ route('library.index', ['type' => 'podcast']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors whitespace-nowrap {{ request('type') == 'podcast' ? 'bg-rose-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Podcasts</a>
                        <a href="{{ route('library.index', ['type' => 'video']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors whitespace-nowrap {{ request('type') == 'video' ? 'bg-amber-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Vídeos</a>
                    </div>

                    @auth
                        <button onclick="document.getElementById('addResourceModal').classList.remove('hidden')" class="px-5 py-2.5 bg-slate-900 text-white rounded-full text-sm font-bold hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/20 shrink-0 flex items-center gap-2">
                            <i class="ri-add-line"></i> <span class="hidden sm:inline">Sugerir</span>
                        </button>
                    @endauth
                </div>
            </div>

            @if($resources->isEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 p-12 text-center animate-fade-up shadow-sm mt-8">
                    <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                        <i class="ri-book-open-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">A biblioteca ainda está a ser construída</h3>
                    <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">Ajuda-nos a preencher estas prateleiras. Leste um livro que te mudou a perspetiva? Ouviste um podcast que te acalmou? Partilha com a comunidade.</p>
                    @auth
                        <button onclick="document.getElementById('addResourceModal').classList.remove('hidden')" class="px-6 py-3 bg-indigo-50 text-indigo-600 font-bold rounded-xl hover:bg-indigo-100 transition-colors">
                            Sugerir o Primeiro Recurso
                        </button>
                    @endauth
                </div>
            @else
                <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6 animate-fade-up" style="animation-delay: 0.1s;">
                    @foreach($resources as $resource)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                        <a href="{{ $resource->url }}" target="_blank" class="block relative aspect-[3/2] bg-slate-100 dark:bg-slate-700 overflow-hidden">
                            @if($resource->thumbnail)
                                <img src="{{ $resource->thumbnail }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-{{ $resource->color ?? 'indigo' }}-400 bg-{{ $resource->color ?? 'indigo' }}-50 dark:bg-{{ $resource->color ?? 'indigo' }}-900/20">
                                    <i class="{{ $resource->icon ?? 'ri-links-line' }} text-5xl opacity-50 transition-transform group-hover:scale-110"></i>
                                </div>
                            @endif
                            <div class="absolute top-2 right-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-lg p-1.5 shadow-sm text-{{ $resource->color ?? 'indigo' }}-500">
                                <i class="{{ $resource->icon ?? 'ri-links-line' }}"></i>
                            </div>
                        </a>
                        
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-1 line-clamp-2" title="{{ $resource->title }}">{{ $resource->title }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ $resource->author ?? 'Sugerido pela comunidade' }}</p>
                            
                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-50 dark:border-slate-700/50">
                                <button onclick="voteResource({{ $resource->id }}, this)" 
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $resource->is_voted ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-slate-50 text-slate-500 dark:bg-slate-700 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-600' }}">
                                    <i class="{{ $resource->is_voted ? 'ri-thumb-up-fill' : 'ri-thumb-up-line' }}"></i>
                                    <span class="count">{{ $resource->votes_count ?? 0 }}</span>
                                </button>
                                <a href="{{ $resource->url }}" target="_blank" class="text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-2 bg-slate-50 dark:bg-slate-700 rounded-lg group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/30"><i class="ri-external-link-line"></i></a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-8">
                    {{ $resources->links() }}
                </div>
            @endif
        </div>
    </div>

    @auth
    <div id="addResourceModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl animate-fade-up z-10">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="ri-lightbulb-flash-line text-amber-500"></i> Sugerir Recurso
                </h3>
                <button onclick="document.getElementById('addResourceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
            </div>
            
            <p class="text-sm text-slate-500 mb-6">Conheces algum livro, vídeo ou podcast que ajude na saúde mental? Partilha o link connosco. A nossa equipa irá validá-lo antes de aparecer na biblioteca.</p>
            
            <form action="{{ route('library.store') ?? '#' }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Título</label>
                    <input type="text" name="title" required placeholder="Ex: O Poder do Agora" class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 px-4 py-3 focus:ring-indigo-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Link (URL)</label>
                    <input type="url" name="url" required placeholder="https://..." class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 px-4 py-3 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Tipo de Recurso</label>
                    <select name="type" required class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 px-4 py-3 focus:ring-indigo-500 outline-none">
                        <option value="book">Livro / Artigo</option>
                        <option value="video">Vídeo / YouTube</option>
                        <option value="podcast">Podcast / Áudio</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-4 mt-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    Enviar Sugestão
                </button>
            </form>
        </div>
    </div>
    @endauth

    <script>
        async function voteResource(id, btn) {
            @guest
                window.location.href = "{{ route('login') }}";
                return;
            @endguest

            const icon = btn.querySelector('i');
            const countSpan = btn.querySelector('.count');
            let count = parseInt(countSpan.innerText);

            // Optimistic UI (Muda instantaneamente para parecer mais rápido)
            if (icon.classList.contains('ri-thumb-up-line')) {
                icon.className = 'ri-thumb-up-fill';
                btn.classList.remove('bg-slate-50', 'text-slate-500');
                btn.classList.add('bg-indigo-50', 'text-indigo-600');
                countSpan.innerText = count + 1;
            } else {
                icon.className = 'ri-thumb-up-line';
                btn.classList.add('bg-slate-50', 'text-slate-500');
                btn.classList.remove('bg-indigo-50', 'text-indigo-600');
                countSpan.innerText = count - 1;
            }

            try {
                await axios.post(`/biblioteca/${id}/votar`);
            } catch (error) {
                console.error('Erro ao votar');
            }
        }
    </script>
</x-lumina-layout>