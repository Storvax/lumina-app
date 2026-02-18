<x-lumina-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Biblioteca</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Recursos para a tua jornada.</p>
                </div>
                
                <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar">
                    <a href="{{ route('library.index') }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors {{ !request('type') ? 'bg-primary-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Todos</a>
                    <a href="{{ route('library.index', ['type' => 'book']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors {{ request('type') == 'book' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Livros</a>
                    <a href="{{ route('library.index', ['type' => 'podcast']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors {{ request('type') == 'podcast' ? 'bg-rose-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Podcasts</a>
                    <a href="{{ route('library.index', ['type' => 'video']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition-colors {{ request('type') == 'video' ? 'bg-red-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">Vídeos</a>
                </div>
            </div>

            <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($resources as $resource)
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-lg transition-all group">
                    <a href="{{ $resource->url }}" target="_blank" class="block relative aspect-[3/2] bg-slate-100 dark:bg-slate-700 overflow-hidden">
                        @if($resource->thumbnail)
                            <img src="{{ $resource->thumbnail }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-{{ $resource->color }}-400">
                                <i class="{{ $resource->icon }} text-5xl opacity-50"></i>
                            </div>
                        @endif
                        <div class="absolute top-2 right-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-lg p-1.5 shadow-sm text-{{ $resource->color }}-500">
                            <i class="{{ $resource->icon }}"></i>
                        </div>
                    </a>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-slate-800 dark:text-white truncate" title="{{ $resource->title }}">{{ $resource->title }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ $resource->author ?? 'Desconhecido' }}</p>
                        
                        <div class="flex items-center justify-between">
                            <button onclick="voteResource({{ $resource->id }}, this)" 
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $resource->is_voted ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-slate-50 text-slate-500 dark:bg-slate-700 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-600' }}">
                                <i class="{{ $resource->is_voted ? 'ri-thumb-up-fill' : 'ri-thumb-up-line' }}"></i>
                                <span class="count">{{ $resource->votes_count }}</span>
                            </button>
                            <a href="{{ $resource->url }}" target="_blank" class="text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><i class="ri-external-link-line"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $resources->links() }}
            </div>
        </div>
    </div>

    <script>
        async function voteResource(id, btn) {
            const icon = btn.querySelector('i');
            const countSpan = btn.querySelector('.count');
            let count = parseInt(countSpan.innerText);

            // Optimistic UI
            if (icon.classList.contains('ri-thumb-up-line')) {
                // Votar
                icon.className = 'ri-thumb-up-fill';
                btn.classList.remove('bg-slate-50', 'text-slate-500');
                btn.classList.add('bg-primary-50', 'text-primary-600');
                countSpan.innerText = count + 1;
            } else {
                // Remover Voto
                icon.className = 'ri-thumb-up-line';
                btn.classList.add('bg-slate-50', 'text-slate-500');
                btn.classList.remove('bg-primary-50', 'text-primary-600');
                countSpan.innerText = count - 1;
            }

            try {
                await axios.post(`/biblioteca/${id}/votar`);
            } catch (error) {
                console.error('Erro ao votar');
                // Revert on error logic here if needed
            }
        }
    </script>
</x-lumina-layout>