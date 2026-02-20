<x-lumina-layout :title="$post->title . ' | Lumina'">

    {{-- ESTILOS DE IMPRESSÃO (Modo Zen) --}}
    <x-slot name="css">
        <style>
            @media print {
                nav, .fixed, .sidebar-col, .comments-section, .post-footer, .no-print { display: none !important; }
                body, article, .glass-card { background: white !important; color: black !important; box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 !important; }
                .main-col { width: 100% !important; grid-column: span 12 !important; }
                h1 { font-size: 24pt !important; color: black !important; }
                p { font-size: 12pt !important; line-height: 1.5 !important; color: #333 !important; }
                .print-footer { display: block !important; margin-top: 50px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10pt; color: #666; text-align: center; }
            }
            .print-footer { display: none; }
        </style>
    </x-slot>

    @php
        // 1. CORES E ÍCONES
        $colors = match($post->tag) {
            'hope' => 'from-emerald-500 to-teal-400',
            'vent' => 'from-rose-500 to-pink-500',
            'anxiety' => 'from-amber-400 to-orange-500',
            default => 'from-indigo-500 to-blue-500'
        };
        $bgTheme = match($post->tag) {
            'hope' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'vent' => 'bg-rose-50 text-rose-700 border-rose-100',
            'anxiety' => 'bg-amber-50 text-amber-700 border-amber-100',
            default => 'bg-indigo-50 text-indigo-700 border-indigo-100'
        };
        $icon = match($post->tag) {
            'hope' => 'ri-seedling-fill',
            'vent' => 'ri-heart-pulse-fill',
            'anxiety' => 'ri-flashlight-fill',
            default => 'ri-chat-smile-fill'
        };

        // 2. DETEÇÃO AUTOMÁTICA DE CRISE (Para a Sidebar)
        $riskKeywords = ['suicidio', 'suicídio', 'morte', 'morrer', 'matar', 'desaparecer', 'acabar com tudo', 'sangue', 'cortar', 'não aguento mais'];
        $fullText = strtolower($post->title . ' ' . $post->content);
        $showCrisisBanner = \Illuminate\Support\Str::contains($fullText, $riskKeywords);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <nav class="flex items-center justify-between mb-8 animate-fade-up no-print">
            <div class="flex items-center gap-2 text-sm text-slate-400">
                <a href="{{ route('forum.index') }}" class="hover:text-indigo-600 transition-colors"><i class="ri-arrow-left-line"></i> Voltar ao Mural</a>
                <span>/</span>
                <span class="font-medium text-slate-600">{{ ucfirst($post->tag) }}</span>
            </div>
        </nav>

        <div class="grid lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 space-y-8 main-col">
                
                <div class="fixed top-0 left-0 w-full h-1 z-[60] no-print">
                    <div id="reading-progress" class="h-full bg-gradient-to-r {{ $colors }} w-0 transition-all duration-100 ease-out"></div>
                </div>

                <article class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-visible border border-white/50 relative animate-fade-up isolate">
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-gradient-to-br {{ $colors }} opacity-10 rounded-full blur-3xl -z-10 no-print"></div>
                    <div class="h-2 w-full bg-gradient-to-r {{ $colors }} no-print"></div>

                    @if($post->is_pinned)
                        <div class="bg-indigo-50 text-indigo-600 text-xs font-bold px-8 py-2 flex items-center gap-2 no-print">
                            <i class="ri-pushpin-fill"></i> Post Fixado pelos Moderadores
                        </div>
                    @endif

                    <div class="p-8 md:p-12">
                        <div class="flex items-start justify-between mb-10 border-b border-slate-100 pb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-white border-2 border-slate-50 text-slate-600 flex items-center justify-center font-bold text-2xl uppercase shadow-sm">
                                    {{ substr($post->user->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-lg">{{ $post->user->name ?? 'Membro Lumina' }}</p>
                                    <p class="text-xs text-slate-400 font-medium flex items-center gap-2 mt-1">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-500">Autor</span>
                                        <span>•</span>
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                        
                                        @if($post->updated_at->gt($post->created_at))
                                            <span class="text-slate-300">•</span>
                                            <span class="italic text-slate-400 cursor-help" title="Editado em {{ $post->updated_at->format('d/m/Y às H:i') }}">
                                                (editado)
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 no-print">
                                <div class="hidden sm:flex items-center gap-2 px-4 py-1.5 rounded-full border {{ $bgTheme }}">
                                    <i class="{{ $icon }}"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">{{ $post->tag }}</span>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" @click.outside="open = false" 
                                            class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-400 transition-all shadow-sm">
                                        <i class="ri-more-2-fill text-lg"></i>
                                    </button>

                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden py-2"
                                         style="display: none;">
                                        
                                        <button onclick="copyLink()" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-3 group">
                                            <i class="ri-link-m text-lg group-hover:scale-110 transition-transform"></i> Copiar Link
                                        </button>
                                        <button onclick="window.print()" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-3 group">
                                            <i class="ri-printer-line text-lg group-hover:scale-110 transition-transform"></i> Imprimir / PDF
                                        </button>

                                        @auth
                                            <div class="h-px bg-slate-100 my-1"></div>
                                            
                                            <button onclick="toggleSubscribe({{ $post->id }}, this)" 
                                                    class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-3 group">
                                                <i class="{{ $post->isSubscribedBy(Auth::user()) ? 'ri-notification-3-fill text-indigo-500' : 'ri-notification-3-line' }} text-lg group-hover:scale-110 transition-transform icon-bell"></i>
                                                <span class="text-subscribe">{{ $post->isSubscribedBy(Auth::user()) ? 'Notificações Ativas' : 'Ativar Notificações' }}</span>
                                            </button>

                                            <button onclick="toggleSave({{ $post->id }}, this)" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-3 group">
                                                <i class="{{ Auth::user()->savedPosts->contains($post->id) ? 'ri-bookmark-fill text-indigo-600' : 'ri-bookmark-line' }} text-lg group-hover:scale-110 transition-transform"></i>
                                                <span class="save-text">{{ Auth::user()->savedPosts->contains($post->id) ? 'Remover dos Guardados' : 'Guardar Post' }}</span>
                                            </button>

                                            @if(Auth::user()->isModerator())
                                                <div class="h-px bg-slate-100 my-1"></div>
                                                <form action="{{ route('forum.pin', $post) }}" method="POST"> @csrf @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-3"><i class="ri-pushpin-line text-lg"></i> {{ $post->is_pinned ? 'Desafixar' : 'Fixar no Topo' }}</button>
                                                </form>
                                                <form action="{{ route('forum.lock', $post) }}" method="POST"> @csrf @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-3"><i class="{{ $post->is_locked ? 'ri-lock-unlock-line' : 'ri-lock-line' }} text-lg"></i> {{ $post->is_locked ? 'Destrancar' : 'Trancar' }}</button>
                                                </form>
                                                @if(!$post->user->isShadowbanned())
                                                    <button onclick="shadowbanUser({{ $post->user->id }}, '{{ $post->user->name }}')" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-500 hover:bg-slate-50 flex items-center gap-3"><i class="ri-ghost-line text-lg"></i> Shadowban</button>
                                                @endif
                                            @endif

                                            @if(Auth::id() === $post->user_id)
                                                <div class="h-px bg-slate-100 my-1"></div>
                                                <button onclick="openEditModal({{ $post->id }}, '{{ e($post->title) }}', '{{ e($post->content) }}', '{{ $post->tag }}', {{ $post->is_sensitive ? 1 : 0 }})" class="w-full text-left px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-3"><i class="ri-pencil-line text-lg"></i> Editar</button>
                                            @endif

                                            @if(Auth::user()->isModerator() || Auth::id() === $post->user_id)
                                                <button onclick="openDeleteModal({{ $post->id }})" class="w-full text-left px-5 py-3 text-sm font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-3"><i class="ri-delete-bin-line text-lg"></i> Eliminar</button>
                                            @endif
                                            @if(Auth::id() !== $post->user_id && !Auth::user()->isModerator())
                                                <div class="h-px bg-slate-100 my-1"></div>
                                                <button onclick="openReportModal({{ $post->id }})" class="w-full text-left px-5 py-3 text-sm font-bold text-amber-600 hover:bg-amber-50 flex items-center gap-3"><i class="ri-flag-line text-lg"></i> Denunciar</button>
                                            @endif
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-8 leading-[1.15] tracking-tight">
                            @if($post->is_locked) <i class="ri-lock-fill text-amber-500" title="Trancado"></i> @endif
                            {{ $post->title }}
                        </h1>
                        
                        <div id="post-content-body" class="prose prose-lg prose-slate max-w-none text-slate-600 leading-relaxed first-letter:text-7xl first-letter:font-bold first-letter:text-slate-900 first-letter:mr-3 first-letter:float-left first-letter:leading-[0.8]">
                            {!! nl2br(e($post->content)) !!}
                        </div>

                        <div class="print-footer">
                            <p>Lumina - Mural da Esperança</p>
                            <p>Originalmente publicado em: {{ url()->current() }}</p>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-100 post-footer no-print">
                            @if($post->is_locked)
                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                                    <p class="text-amber-800 font-bold flex items-center justify-center gap-2"><i class="ri-lock-line text-xl"></i> Este post foi trancado.</p>
                                </div>
                            @else
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 text-center">Como esta história te fez sentir?</p>
                                <div class="flex justify-center flex-wrap items-center gap-4">
                                    <button onclick="react({{ $post->id }}, 'hug', this)" class="group relative flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-white border-2 border-slate-100 hover:border-rose-400 hover:bg-rose-50 transition-all duration-300">
                                        <span class="text-3xl mb-1 group-hover:scale-110 transition-transform">🫂</span>
                                        <span class="text-xs font-bold text-slate-400 group-hover:text-rose-600 count-hug">{{ $post->reactions->where('type', 'hug')->count() }}</span>
                                    </button>
                                    <button onclick="react({{ $post->id }}, 'candle', this)" class="group relative flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-white border-2 border-slate-100 hover:border-amber-400 hover:bg-amber-50 transition-all duration-300">
                                        <span class="text-3xl mb-1 group-hover:scale-110 transition-transform">🕯️</span>
                                        <span class="text-xs font-bold text-slate-400 group-hover:text-amber-600 count-candle">{{ $post->reactions->where('type', 'candle')->count() }}</span>
                                    </button>
                                    <button onclick="react({{ $post->id }}, 'ear', this)" class="group relative flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-white border-2 border-slate-100 hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-300">
                                        <span class="text-3xl mb-1 group-hover:scale-110 transition-transform">👂</span>
                                        <span class="text-xs font-bold text-slate-400 group-hover:text-indigo-600 count-ear">{{ $post->reactions->where('type', 'ear')->count() }}</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>

                <div class="bg-slate-50 rounded-[2rem] border border-slate-200 p-6 md:p-8 animate-fade-up comments-section no-print" style="animation-delay: 0.1s;">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="ri-discuss-line text-indigo-500"></i> Conversa de Apoio <span class="bg-indigo-100 text-indigo-600 text-xs px-2 py-1 rounded-full">{{ $post->comments->count() }}</span>
                    </h3>

                    @if(!$post->is_locked)
                        <form action="{{ route('forum.comment', $post) }}" method="POST" class="group relative mb-10">
                            @csrf
                            <div class="absolute left-4 top-4 w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md z-10">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <textarea name="body" rows="3" placeholder="Escreve uma mensagem de apoio..." class="w-full pl-16 pr-4 py-4 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 resize-none transition-all shadow-sm group-focus-within:shadow-md"></textarea>
                            <div class="absolute bottom-3 right-3">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg shadow-indigo-200 transition-transform active:scale-95 flex items-center gap-2">
                                    <span>Enviar</span> <i class="ri-send-plane-fill"></i>
                                </button>
                            </div>
                        </form>
                    @endif

                    <div class="space-y-8">
                        @forelse($post->comments as $comment)
                            <div class="group relative" id="comment-{{ $comment->id }}">
                                @if($comment->replies->count() > 0)
                                    <div class="absolute left-5 top-12 bottom-0 w-0.5 bg-slate-200 group-hover:bg-indigo-200 transition-colors"></div>
                                @endif

                                <div class="flex gap-4">
                                    <div class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm shrink-0 shadow-sm z-10 relative">
                                        {{ substr($comment->user->name, 0, 1) }}
                                        @if($comment->is_helpful)
                                            <div class="absolute -top-1 -right-1 bg-amber-400 text-white rounded-full p-0.5 border-2 border-white"><i class="ri-star-fill text-[8px]"></i></div>
                                        @endif
                                    </div>

                                    <div class="flex-1 max-w-2xl">
                                        <div class="bg-white p-5 rounded-2xl rounded-tl-none border {{ $comment->is_helpful ? 'border-amber-200 bg-amber-50/30' : 'border-slate-100' }} shadow-sm relative">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-800 text-sm">{{ $comment->user->name }}</span>
                                                    @if($comment->user_id === $post->user_id)
                                                        <span class="text-[10px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold border border-indigo-100">Autor</span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-slate-600 text-sm leading-relaxed">{{ $comment->body }}</p>

                                            <div class="flex items-center justify-between mt-3 pt-3 border-t {{ $comment->is_helpful ? 'border-amber-100' : 'border-slate-50' }}" x-data="{ replying: false }">
                                                <div class="flex gap-2">
                                                    <button onclick="reactComment({{ $comment->id }}, 'heart', this)" class="text-xs font-bold text-slate-400 hover:text-rose-500 px-2 py-1 rounded-lg transition-all flex items-center gap-1">
                                                        <span>❤️</span> <span class="count">{{ $comment->reactions->where('type', 'heart')->count() ?: '' }}</span>
                                                    </button>
                                                    <button onclick="reactComment({{ $comment->id }}, 'muscle', this)" class="text-xs font-bold text-slate-400 hover:text-amber-500 px-2 py-1 rounded-lg transition-all flex items-center gap-1">
                                                        <span>💪</span> <span class="count">{{ $comment->reactions->where('type', 'muscle')->count() ?: '' }}</span>
                                                    </button>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    @auth
                                                        @if(Auth::id() === $post->user_id && Auth::id() !== $comment->user_id)
                                                            <form action="{{ route('comments.helpful', $comment) }}" method="POST"> @csrf
                                                                <button type="submit" class="text-xs font-bold {{ $comment->is_helpful ? 'text-amber-500' : 'text-slate-300 hover:text-amber-500' }} transition-colors"><i class="{{ $comment->is_helpful ? 'ri-star-fill' : 'ri-star-line' }}"></i> Ajudou-me</button>
                                                            </form>
                                                        @endif
                                                        <button @click="replying = !replying" class="text-xs font-bold text-indigo-500 hover:text-indigo-700 transition-colors">Responder</button>
                                                    @endauth
                                                </div>

                                                <div x-show="replying" x-transition class="absolute top-full left-0 w-full mt-2 z-20 bg-white p-3 rounded-xl shadow-xl border border-indigo-100" style="display: none;">
                                                    <form action="{{ route('forum.comment', $post) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                        <textarea name="body" rows="2" placeholder="A tua resposta..." class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-indigo-500 outline-none resize-none mb-2" required></textarea>
                                                        <div class="flex justify-end gap-2">
                                                            <button type="button" @click="replying = false" class="text-xs font-bold text-slate-400">Cancelar</button>
                                                            <button type="submit" class="text-xs font-bold bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700">Responder</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        @if($comment->replies->count() > 0)
                                            <div class="mt-4 space-y-4 pl-4">
                                                @foreach($comment->replies as $reply)
                                                    <div class="flex gap-3">
                                                        <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center font-bold text-xs shrink-0">{{ substr($reply->user->name, 0, 1) }}</div>
                                                        <div class="flex-1 bg-slate-50/80 p-4 rounded-xl rounded-tl-none border border-slate-100">
                                                            <div class="flex justify-between items-start mb-1">
                                                                <span class="font-bold text-slate-700 text-xs">{{ $reply->user->name }}</span>
                                                                <span class="text-[9px] font-bold text-slate-300 uppercase">{{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>
                                                            <p class="text-slate-600 text-xs leading-relaxed">{{ $reply->body }}</p>
                                                            <div class="flex gap-2 mt-2">
                                                                <button onclick="reactComment({{ $reply->id }}, 'heart', this)" class="text-[10px] font-bold text-slate-400 hover:text-rose-500 transition-all flex items-center gap-1">❤️ <span class="count">{{ $reply->reactions->where('type', 'heart')->count() ?: '' }}</span></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 opacity-60">
                                <i class="ri-chat-voice-line text-4xl text-slate-300 mb-2 block"></i>
                                <p class="text-sm font-medium text-slate-500">Ainda ninguém partilhou apoio.</p>
                                <p class="text-xs text-slate-400">Sê a primeira voz amiga.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6 sidebar-col no-print">
                
                @if($showCrisisBanner)
                    <div class="bg-rose-50 border border-rose-100 rounded-3xl p-6 shadow-sm animate-fade-up">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center text-xl shrink-0">
                                <i class="ri-alarm-warning-fill"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-rose-700 text-sm mb-1">Não estás sozinho(a)</h4>
                                <p class="text-xs text-rose-600/80 mb-3 leading-relaxed">
                                    Parece que estás a passar por um momento difícil. Há ajuda disponível agora mesmo.
                                </p>
                                <div class="space-y-2">
                                    <a href="tel:112" class="flex items-center justify-between px-3 py-2 bg-white rounded-xl border border-rose-100 shadow-sm hover:shadow-md transition-all group">
                                        <span class="text-xs font-bold text-slate-700">Emergência (112)</span>
                                        <i class="ri-phone-fill text-rose-500"></i>
                                    </a>
                                    <a href="tel:808242424" class="flex items-center justify-between px-3 py-2 bg-white rounded-xl border border-blue-100 shadow-sm hover:shadow-md transition-all group">
                                        <span class="text-xs font-bold text-slate-700">SNS 24 (Apoio)</span>
                                        <i class="ri-phone-fill text-blue-500"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-3xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 animate-fade-up" style="animation-delay: 0.2s;">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Quem escreve</h4>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br {{ $colors }} p-1 shrink-0">
                            <div class="w-full h-full bg-white rounded-full flex items-center justify-center text-xl font-bold text-slate-700">
                                {{ substr($post->user->name, 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-lg leading-tight">{{ $post->user->name }}</p>
                            <div class="flex items-center gap-1.5 mt-1 text-slate-500 text-xs font-medium">
                                <i class="ri-calendar-smile-line text-indigo-400"></i>
                                <span>Na comunidade desde {{ $post->user->created_at->translatedFormat('M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($post->user->bio)
                        <div class="mb-6 relative">
                            <i class="ri-double-quotes-l absolute -top-2 -left-1 text-indigo-100 text-3xl -z-10"></i>
                            <p class="text-sm text-slate-600 italic leading-relaxed pl-2 relative z-10">
                                "{{ $post->user->bio }}"
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3 text-center border-t border-slate-100 pt-4">
                        <div class="bg-slate-50 rounded-xl p-2">
                            <span class="block font-bold text-slate-800 text-lg">{{ $post->user->posts->count() }}</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wide">Histórias</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-2">
                            <span class="block font-bold text-slate-800 text-lg">{{ $post->user->comments_count ?? 0 }}</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wide">Apoios</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-6 text-white text-center shadow-xl shadow-indigo-200 relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
                    <div class="relative z-10" id="breathe-widget">
                        <div class="flex justify-between items-start mb-4"><i class="ri-lungs-line text-2xl opacity-80"></i><span class="text-[10px] bg-white/20 px-2 py-1 rounded-full uppercase tracking-wider font-bold">Calma</span></div>
                        <div class="relative w-32 h-32 mx-auto mb-6 flex items-center justify-center">
                            <div id="breathe-ring-1" class="absolute inset-0 border-4 border-white/10 rounded-full transition-all duration-[4000ms]"></div>
                            <div id="breathe-ring-2" class="absolute inset-4 border-4 border-white/20 rounded-full transition-all duration-[4000ms]"></div>
                            <div id="breathe-circle" class="relative z-10 w-16 h-16 bg-white text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg shadow-2xl transition-all duration-[4000ms] cursor-pointer hover:scale-105" onclick="toggleBreathing()">
                                <i class="ri-play-fill text-2xl" id="breathe-icon"></i><span id="breathe-text" class="hidden text-xs">4s</span>
                            </div>
                        </div>
                        <h4 id="breathe-instruction" class="font-bold text-lg mb-1">Precisas de uma pausa?</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(in_array($post->tag, ['vent', 'anxiety']))
        <div id="emotional-checkin" class="fixed bottom-6 right-6 z-[70] hidden animate-fade-up no-print">
            <div class="bg-white border border-indigo-100 shadow-2xl rounded-2xl p-5 max-w-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-400 to-violet-400"></div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0"><i class="ri-heart-pulse-line"></i></div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Como te sentes depois de ler?</h4>
                        <p class="text-xs text-slate-500 mb-3 leading-relaxed">Este conteúdo foi intenso. Queremos garantir que estás bem.</p>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="closeCheckin()" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition-colors">Estou bem 👍</button>
                            <button onclick="triggerBreathingFromCheckin()" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold hover:bg-indigo-100 transition-colors">Pausa 🍃</button>
                            <button id="sosBtnTriggerCheckin" class="px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 text-xs font-bold hover:bg-rose-50 transition-colors">Ajuda 🆘</button>
                        </div>
                    </div>
                    <button onclick="closeCheckin()" class="text-slate-300 hover:text-slate-500"><i class="ri-close-line"></i></button>
                </div>
            </div>
        </div>
    @endif

    <div id="postModal" class="fixed inset-0 z-[80] hidden"><div id="postModalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="togglePostModal()"></div><div class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none"><div id="postModalPanel" class="bg-white md:rounded-[2rem] rounded-t-[2rem] shadow-2xl w-full max-w-lg md:mx-4 transform pointer-events-auto p-6"><form id="create-post-form" class="space-y-4">@csrf<h3 class="text-xl font-bold">Editar Post</h3><input name="title" class="w-full bg-slate-50 p-3 rounded-xl border border-slate-200"><textarea name="content" class="w-full bg-slate-50 p-3 rounded-xl border border-slate-200" rows="5"></textarea><div class="hidden"><input type="radio" name="tag" value="hope"><input type="radio" name="tag" value="vent"><input type="radio" name="tag" value="anxiety"><input type="checkbox" name="is_sensitive"></div><button class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold">Guardar</button></form></div></div></div>
    <div id="deleteModal" class="fixed inset-0 z-[90] hidden"><div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div><div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none"><div id="deletePanel" class="bg-white rounded-3xl p-6 pointer-events-auto text-center max-w-sm"><h3 class="text-xl font-bold mb-2">Apagar?</h3><p class="text-slate-500 mb-4">Esta ação é irreversível.</p><div class="flex gap-2"><button onclick="closeDeleteModal()" class="flex-1 py-2 bg-slate-100 rounded-xl">Cancelar</button><button id="confirm-delete-btn" class="flex-1 py-2 bg-rose-500 text-white rounded-xl">Apagar</button></div></div></div></div>
    <div id="reportModal" class="fixed inset-0 z-[90] hidden"><div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeReportModal()"></div><div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none"><div id="reportPanel" class="bg-white rounded-3xl p-6 pointer-events-auto max-w-sm"><h3 class="text-xl font-bold mb-4">Denunciar</h3><button onclick="submitReport('spam')" class="w-full p-3 text-left hover:bg-slate-50 rounded-xl">🤖 Spam</button><button onclick="submitReport('hate')" class="w-full p-3 text-left hover:bg-slate-50 rounded-xl">🤬 Ódio</button><button onclick="submitReport('risk')" class="w-full p-3 text-left hover:bg-slate-50 rounded-xl text-rose-600">🆘 Risco</button></div></div></div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            function copyLink() { 
                navigator.clipboard.writeText(window.location.href).then(() => { 
                    Swal.fire({ title: 'Copiado!', text: 'Link copiado para a área de transferência.', icon: 'success', timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-3xl' }});
                }).catch(console.error); 
            }

            document.addEventListener('DOMContentLoaded', () => {
                const checkin = document.getElementById('emotional-checkin');
                if (!checkin || sessionStorage.getItem('emotionalCheckinShown')) return;
                let shown = false;
                const showIt = () => { if(shown) return; checkin.classList.remove('hidden'); shown = true; sessionStorage.setItem('emotionalCheckinShown', 'true'); };
                window.addEventListener('scroll', () => { if((window.scrollY + window.innerHeight) / document.documentElement.scrollHeight > 0.8) showIt(); });
                setTimeout(showIt, 45000); 
                window.closeCheckin = () => { checkin.classList.add('opacity-0', 'translate-y-4'); setTimeout(() => checkin.remove(), 500); };
                window.triggerBreathingFromCheckin = () => { closeCheckin(); document.getElementById('breathe-widget').scrollIntoView({behavior: 'smooth', block: 'center'}); setTimeout(toggleBreathing, 1000); };
                const sosBtn = document.getElementById('sosBtnTriggerCheckin');
                if(sosBtn) sosBtn.addEventListener('click', () => { closeCheckin(); document.getElementById('sosModal').classList.remove('hidden'); });
            });
            
            let breatheInterval, isBreathing = false;
            function toggleBreathing() {
                const circle = document.getElementById('breathe-circle'), ring1 = document.getElementById('breathe-ring-1'), icon = document.getElementById('breathe-icon'), textSpan = document.getElementById('breathe-text'), title = document.getElementById('breathe-instruction');
                if (isBreathing) { clearInterval(breatheInterval); isBreathing = false; icon.classList.remove('hidden'); textSpan.classList.add('hidden'); circle.style.transform = 'scale(1)'; ring1.style.transform = 'scale(1)'; title.innerText = "Pausa terminada"; setTimeout(() => { title.innerText = "Precisas de uma pausa?"; }, 2000); } 
                else { isBreathing = true; icon.classList.add('hidden'); textSpan.classList.remove('hidden'); let phase = 0; function runPhase() { if(!isBreathing) return; if (phase === 0) { title.innerText = "Inspira..."; textSpan.innerText = "Inspira"; circle.style.transform = 'scale(1.5)'; ring1.style.transform = 'scale(1.8)'; phase = 1; } else if (phase === 1) { title.innerText = "Segura..."; textSpan.innerText = "Segura"; phase = 2; } else { title.innerText = "Expira..."; textSpan.innerText = "Expira"; circle.style.transform = 'scale(1)'; ring1.style.transform = 'scale(1)'; phase = 0; } } runPhase(); breatheInterval = setInterval(runPhase, 4000); }
            }
            
            window.react = async function(pid, t, btn) { btn.classList.add('scale-110'); setTimeout(()=>btn.classList.remove('scale-110'),200); try{await axios.post(`/mural/${pid}/reagir`,{type:t}); let c=btn.querySelector('.count-hug,.count-candle,.count-ear'); c.innerText=parseInt(c.innerText)+1;}catch(e){} };
            window.reactComment = async function(cid, t, btn) { btn.classList.add('scale-110'); setTimeout(()=>btn.classList.remove('scale-110'),200); try{const r=await axios.post(`/comentarios/${cid}/reagir`,{type:t}); let c=btn.querySelector('.count'); let v=parseInt(c.innerText)||0; c.innerText=r.data.action==='added'?v+1:(v>0?v-1:'');}catch(e){} };
            
            window.toggleSubscribe = async function(postId, btn) {
                const icon = btn.querySelector('.icon-bell');
                const text = btn.querySelector('.text-subscribe');
                const wasSubscribed = icon.classList.contains('ri-notification-3-fill');
                if (wasSubscribed) { icon.className = 'ri-notification-3-line text-lg group-hover:scale-110 transition-transform icon-bell'; text.innerText = 'Receber notificações'; icon.classList.remove('text-indigo-500'); } else { icon.className = 'ri-notification-3-fill text-lg group-hover:scale-110 transition-transform icon-bell text-indigo-500'; text.innerText = 'Notificações Ativas'; }
                try { await axios.post(`/mural/${postId}/subscrever`); } catch (error) { Swal.fire({ title: 'Erro!', text: 'Erro ao subscrever.', icon: 'error', customClass: { popup: 'rounded-3xl' } }); }
            };

            const postModal=document.getElementById('postModal'), deleteModal=document.getElementById('deleteModal'), reportModal=document.getElementById('reportModal');
            let delId=null, repId=null, editId=null;
            window.togglePostModal=()=>{postModal.classList.toggle('hidden')}; window.closeDeleteModal=()=>{deleteModal.classList.add('hidden')}; window.closeReportModal=()=>{reportModal.classList.add('hidden')};
            
            window.openEditModal=(id,t,c,tag,s)=>{editId=id; document.querySelector('#create-post-form input[name="title"]').value=t; document.querySelector('#create-post-form textarea').value=c; togglePostModal(); };
            window.openDeleteModal=(id)=>{delId=id; deleteModal.classList.remove('hidden');};
            window.openReportModal=(id)=>{repId=id; reportModal.classList.remove('hidden');};
            
            document.getElementById('create-post-form').addEventListener('submit', async(e)=>{e.preventDefault(); try{const fd=new FormData(e.target); fd.append('_method','PATCH'); await axios.post(`/mural/${editId}`, fd); location.reload();}catch(e){Swal.fire({ title: 'Erro!', text: 'Ocorreu um erro ao guardar.', icon: 'error', customClass: { popup: 'rounded-3xl' }});}});
            document.getElementById('confirm-delete-btn').addEventListener('click', async()=>{try{await axios.delete(`/mural/${delId}`); location.href='/mural';}catch(e){Swal.fire({ title: 'Erro!', text: 'Erro ao apagar.', icon: 'error', customClass: { popup: 'rounded-3xl' }});}});
            
            window.submitReport=async(r)=>{
                try{
                    await axios.post(`/mural/${repId}/report`,{reason:r}); 
                    closeReportModal(); 
                    Swal.fire({ title: 'Denúncia Enviada', text: 'Obrigado por ajudares a manter a comunidade segura.', icon: 'success', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-3xl' }});
                }catch(e){
                    Swal.fire({ title: 'Erro!', text: 'Erro ao enviar denúncia.', icon: 'error', customClass: { popup: 'rounded-3xl' }});
                }
            };
            
            window.toggleSave=async(id,btn)=>{try{const r=await axios.post(`/mural/${id}/save`); btn.querySelector('.save-text').innerText=r.data.saved?'Remover':'Guardar';}catch(e){}};

            window.shadowbanUser = async function(userId, userName) {
                const result = await Swal.fire({
                    title: 'Ativar Shadowban?',
                    text: `Tens a certeza que queres ativar o Shadowban para ${userName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sim, ativar',
                    cancelButtonText: 'Cancelar',
                    customClass: { popup: 'rounded-3xl' }
                });

                if(!result.isConfirmed) return;

                try {
                    await axios.post(`/users/${userId}/shadowban`);
                    await Swal.fire({ title: 'Ativado', text: `${userName} está agora em modo fantasma.`, icon: 'success', customClass: { popup: 'rounded-3xl' }});
                    window.location.reload();
                } catch (error) {
                    Swal.fire({ title: 'Erro!', text: 'Erro ao aplicar shadowban.', icon: 'error', customClass: { popup: 'rounded-3xl' }});
                }
            };
        </script>
    </x-slot>

</x-lumina-layout>