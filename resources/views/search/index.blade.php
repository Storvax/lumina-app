<x-lumina-layout title="Pesquisar | Lumina">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- HERO + BARRA DE PESQUISA --}}
        <div class="text-center mb-10 animate-fade-up">
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-3">
                Encontrar<span class="text-indigo-500">.</span>
            </h1>
            <p class="text-slate-500 text-sm md:text-base mb-8">Histórias, recursos e salas — tudo num só lugar.</p>

            <form method="GET" action="{{ route('search.index') }}" class="relative max-w-xl mx-auto">
                {{-- Preservar filtros activos durante a pesquisa --}}
                @if($emotion)<input type="hidden" name="emotion" value="{{ $emotion }}">@endif
                @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
                @if(! $safe)<input type="hidden" name="safe" value="0">@endif

                <i class="ri-search-line absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                <input type="text"
                       name="q"
                       value="{{ $query }}"
                       placeholder="O que procuras?"
                       autocomplete="off"
                       autofocus
                       class="w-full pl-12 pr-4 py-4 rounded-2xl border-2 border-slate-200 bg-white/80 backdrop-blur-sm focus:border-indigo-500 focus:ring-0 text-slate-800 font-medium shadow-sm transition-all placeholder:text-slate-400">
            </form>
        </div>

        {{-- FILTROS --}}
        <div class="flex flex-wrap justify-center gap-2 mb-10 animate-fade-up" style="animation-delay: 0.05s;">
            {{-- Filtro por emoção --}}
            @php
                $emotions = [
                    'hope'    => ['label' => 'Esperança', 'emoji' => '🌱', 'active' => 'bg-emerald-100 border-emerald-300 text-emerald-700'],
                    'vent'    => ['label' => 'Desabafo',  'emoji' => '❤️‍🩹', 'active' => 'bg-rose-100 border-rose-300 text-rose-700'],
                    'anxiety' => ['label' => 'Ansiedade', 'emoji' => '🌩️', 'active' => 'bg-amber-100 border-amber-300 text-amber-700'],
                ];
            @endphp

            @foreach($emotions as $tag => $meta)
                @php
                    $isActive = $emotion === $tag;
                    $href = $isActive
                        ? route('search.index', array_filter(['q' => $query, 'type' => $type, 'safe' => $safe ? null : '0']))
                        : route('search.index', array_filter(['q' => $query, 'emotion' => $tag, 'type' => $type, 'safe' => $safe ? null : '0']));
                @endphp
                <a href="{{ $href }}"
                   class="px-3 py-1.5 rounded-full border text-xs font-bold transition-all {{ $isActive ? $meta['active'] : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300' }}">
                    {{ $meta['emoji'] }} {{ $meta['label'] }}
                </a>
            @endforeach

            <span class="w-px h-6 bg-slate-200 mx-1 self-center"></span>

            {{-- Filtro por tipo de conteúdo --}}
            @php
                $types = [
                    'posts'     => ['label' => 'Mural',      'icon' => 'ri-quill-pen-line'],
                    'resources' => ['label' => 'Biblioteca',  'icon' => 'ri-book-read-line'],
                    'rooms'     => ['label' => 'Salas',       'icon' => 'ri-fire-line'],
                ];
            @endphp

            @foreach($types as $key => $meta)
                @php
                    $isActive = $type === $key;
                    $href = $isActive
                        ? route('search.index', array_filter(['q' => $query, 'emotion' => $emotion, 'safe' => $safe ? null : '0']))
                        : route('search.index', array_filter(['q' => $query, 'emotion' => $emotion, 'type' => $key, 'safe' => $safe ? null : '0']));
                @endphp
                <a href="{{ $href }}"
                   class="px-3 py-1.5 rounded-full border text-xs font-bold transition-all flex items-center gap-1.5 {{ $isActive ? 'bg-indigo-100 border-indigo-300 text-indigo-700' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300' }}">
                    <i class="{{ $meta['icon'] }} text-sm"></i> {{ $meta['label'] }}
                </a>
            @endforeach

            @if($hasQuery)
                <span class="w-px h-6 bg-slate-200 mx-1 self-center"></span>

                {{-- Toggle conteúdo sensível --}}
                <a href="{{ route('search.index', array_filter(['q' => $query, 'emotion' => $emotion, 'type' => $type, 'safe' => $safe ? '0' : null])) }}"
                   class="px-3 py-1.5 rounded-full border text-xs font-bold transition-all flex items-center gap-1.5 {{ !$safe ? 'bg-slate-700 border-slate-700 text-white' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300' }}">
                    <i class="ri-eye-{{ $safe ? 'off-' : '' }}line text-sm"></i> {{ $safe ? 'Incluir sensíveis' : 'Sensíveis visíveis' }}
                </a>
            @endif
        </div>

        {{-- RESULTADOS --}}
        @if($hasQuery)
            @if($totalResults === 0)
                {{-- ESTADO VAZIO --}}
                <div class="text-center py-16 animate-fade-up">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                        <i class="ri-search-eye-line text-3xl text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 mb-1">Sem resultados para "{{ $query }}"</h3>
                    <p class="text-sm text-slate-400">Tenta outras palavras ou remove os filtros.</p>
                </div>
            @else
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-8">
                    {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }} para "{{ $query }}"
                </p>

                {{-- POSTS DO MURAL --}}
                @if($posts->isNotEmpty())
                    <section class="mb-12 animate-fade-up">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="ri-quill-pen-fill text-indigo-500"></i>
                            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Mural da Esperança</h2>
                            <span class="bg-indigo-100 text-indigo-600 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $posts->count() }}</span>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($posts as $post)
                                @php
                                    $tagColors = match($post->tag) {
                                        'hope'    => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'vent'    => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'anxiety' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default   => 'bg-slate-50 text-slate-600 border-slate-100',
                                    };
                                    $tagEmoji = match($post->tag) {
                                        'hope' => '🌱', 'vent' => '❤️‍🩹', 'anxiety' => '🌩️', default => '💬'
                                    };
                                @endphp
                                <a href="{{ route('forum.show', $post) }}" class="group block bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $tagColors }}">{{ $tagEmoji }} {{ $post->tag }}</span>
                                        <span class="text-[10px] text-slate-300 font-medium">{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                    <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-indigo-600 transition-colors line-clamp-1">{{ $post->title }}</h3>
                                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">{{ Str::limit($post->content, 120) }}</p>
                                    <div class="flex items-center gap-3 mt-3 text-[10px] text-slate-400 font-medium">
                                        <span><i class="ri-chat-1-line"></i> {{ $post->comments->count() }}</span>
                                        <span><i class="ri-heart-line"></i> {{ $post->reactions->count() }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- RECURSOS DA BIBLIOTECA --}}
                @if($resources->isNotEmpty())
                    <section class="mb-12 animate-fade-up" style="animation-delay: 0.05s;">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="ri-book-read-fill text-indigo-500"></i>
                            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Biblioteca</h2>
                            <span class="bg-indigo-100 text-indigo-600 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $resources->count() }}</span>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($resources as $resource)
                                <a href="{{ $resource->url }}" target="_blank" rel="noopener" class="group block bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-{{ $resource->color }}-50 text-{{ $resource->color }}-500 flex items-center justify-center shrink-0">
                                            <i class="{{ $resource->icon }} text-lg"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors truncate">{{ $resource->title }}</h3>
                                            @if($resource->author)
                                                <p class="text-[10px] text-slate-400 font-medium">{{ $resource->author }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if($resource->description)
                                        <p class="text-xs text-slate-500 leading-relaxed mt-3 line-clamp-2">{{ Str::limit($resource->description, 100) }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-3 text-[10px] text-slate-400 font-medium">
                                        <span class="bg-slate-50 px-2 py-0.5 rounded text-slate-500 capitalize">{{ $resource->type }}</span>
                                        <span><i class="ri-arrow-up-line"></i> {{ $resource->votes_count ?? 0 }} votos</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- SALAS --}}
                @if($rooms->isNotEmpty())
                    <section class="mb-12 animate-fade-up" style="animation-delay: 0.1s;">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="ri-fire-fill text-orange-500"></i>
                            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Salas</h2>
                            <span class="bg-orange-100 text-orange-600 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $rooms->count() }}</span>
                        </div>
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($rooms as $room)
                                <a href="{{ route('chat.show', $room) }}" class="group block bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-100 p-5 shadow-sm hover:shadow-md hover:border-{{ $room->color ?? 'indigo' }}-100 transition-all">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 rounded-xl bg-{{ $room->color ?? 'indigo' }}-50 text-{{ $room->color ?? 'indigo' }}-500 flex items-center justify-center">
                                            <i class="{{ $room->icon ?? 'ri-door-open-line' }} text-lg"></i>
                                        </div>
                                        <h3 class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors">{{ $room->name }}</h3>
                                    </div>
                                    @if($room->description)
                                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">{{ Str::limit($room->description, 100) }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif

        @else
            {{-- ESTADO INICIAL (sem pesquisa) --}}
            <div class="text-center py-16 animate-fade-up" style="animation-delay: 0.1s;">
                <div class="w-20 h-20 rounded-full bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                    <i class="ri-compass-discover-line text-3xl text-indigo-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-1">Explora a comunidade</h3>
                <p class="text-sm text-slate-400 mb-6">Pesquisa por temas, emoções ou palavras-chave.</p>
                <div class="flex flex-wrap justify-center gap-2 max-w-sm mx-auto">
                    @foreach(['ansiedade', 'respiração', 'insónia', 'esperança', 'solidão'] as $suggestion)
                        <a href="{{ route('search.index', ['q' => $suggestion]) }}"
                           class="px-3 py-1.5 rounded-full bg-white border border-slate-200 text-xs font-bold text-slate-500 hover:border-indigo-300 hover:text-indigo-600 transition-all">
                            {{ $suggestion }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</x-lumina-layout>
