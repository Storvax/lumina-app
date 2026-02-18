<section id="biblioteca" class="py-24 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-800 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-12">
            <div class="scroll-reveal">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">A Nossa Biblioteca</h2>
                <p class="text-slate-500 dark:text-slate-400">Recursos curados e votados pela comunidade.</p>
            </div>
            <a href="{{ route('library.index') }}" class="hidden md:block text-primary-600 dark:text-primary-400 font-semibold hover:underline">Explorar tudo</a>
        </div>

        <div class="grid md:grid-cols-4 gap-6">
            
            @if(isset($featuredResources))
                @foreach($featuredResources as $resource)
                <a href="{{ $resource->url }}" target="_blank" class="scroll-reveal group bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-lg dark:hover:shadow-none transition-all border border-slate-100 dark:border-slate-700 block">
                    <div class="relative aspect-[2/3] bg-slate-200 dark:bg-slate-700 rounded-xl mb-4 overflow-hidden">
                        @if($resource->thumbnail)
                            <img src="{{ $resource->thumbnail }}" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500" alt="{{ $resource->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-{{ $resource->color }}-50 dark:bg-{{ $resource->color }}-900/20">
                                <i class="{{ $resource->icon }} text-4xl text-{{ $resource->color }}-400"></i>
                            </div>
                        @endif
                        
                        <div class="absolute top-2 right-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-lg p-1.5 shadow-sm text-{{ $resource->color }}-500">
                            <i class="{{ $resource->icon }}"></i>
                        </div>
                    </div>
                    
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm leading-tight mb-1 truncate">{{ $resource->title }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 truncate">{{ $resource->author ?? 'Comunidade' }}</p>
                    
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-2">
                            <div class="w-6 h-6 rounded-full bg-blue-100 border-2 border-white dark:border-slate-800 flex items-center justify-center text-[8px] font-bold text-blue-600">L</div>
                            <div class="w-6 h-6 rounded-full bg-green-100 border-2 border-white dark:border-slate-800 flex items-center justify-center text-[8px] font-bold text-green-600">M</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">+{{ $resource->votes_count }} votos</span>
                    </div>
                </a>
                @endforeach
            @endif

            <a href="{{ route('library.index') }}" class="scroll-reveal group bg-white dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center p-6 text-center hover:border-primary-300 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-all cursor-pointer">
                <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-700 flex items-center justify-center mb-3 group-hover:bg-white dark:group-hover:bg-slate-600 text-slate-400 dark:text-slate-500 group-hover:text-primary-500 transition-colors">
                    <i class="ri-add-line text-2xl"></i>
                </div>
                <h4 class="font-bold text-slate-700 dark:text-slate-300 text-sm">Adicionar Recurso</h4>
                <p class="text-xs text-slate-400 mt-1">O que te ajudou a ti?</p>
            </a>
        </div>
    </div>
</section>