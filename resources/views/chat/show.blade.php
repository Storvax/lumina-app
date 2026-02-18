<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $room->name }} | Lumina</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        body { background-color: #f0f4f8; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 24px 24px; }
        .glass-panel { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .blur-content { filter: blur(5px); user-select: none; cursor: pointer; transition: 0.3s; }
        .sensitive-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.7); border-radius: 1rem; cursor: pointer; z-index: 10; backdrop-filter: blur(4px); }
        .sensitive-active .blur-content { filter: none; }
        .sensitive-active .sensitive-overlay { display: none; }
        @keyframes floatUp { 0% { transform: translateY(0) scale(0.5); opacity: 0; } 20% { opacity: 1; } 100% { transform: translateY(-80vh) scale(1.5); opacity: 0; } }
        .floating-heart { position: fixed; bottom: 0; font-size: 24px; animation: floatUp 3s ease-in forwards; z-index: 100; pointer-events: none; }
        @keyframes warm-glow { 0% { box-shadow: inset 0 0 0 0 rgba(251, 146, 60, 0); } 50% { box-shadow: inset 0 0 100px 40px rgba(251, 146, 60, 0.2); } 100% { box-shadow: inset 0 0 0 0 rgba(251, 146, 60, 0); } }
        .feel-hug-effect { animation: warm-glow 2s ease-in-out; }
        #support-toast { transform: translateY(-150%); opacity: 0; pointer-events: none; transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55); }
        #support-toast.active { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .sound-btn.active { background-color: rgba(255, 255, 255, 0.3); border: 2px solid white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        #mobile-drawer { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform; }
        #mobile-drawer.open { transform: translateX(0); }
        #mobile-drawer.closed { transform: translateX(100%); }
        .pause-active { filter: grayscale(100%); opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body class="antialiased text-slate-600 font-sans h-full flex overflow-hidden relative selection:bg-indigo-100 selection:text-indigo-700">

    <div id="visual-effects-layer" class="fixed inset-0 pointer-events-none z-[60]"></div>
    
    <div id="support-toast" class="fixed top-4 inset-x-4 mx-auto md:top-6 md:left-1/2 md:-translate-x-1/2 md:w-auto md:inset-x-auto z-[70] bg-white border border-indigo-100 shadow-2xl rounded-2xl px-4 py-3 flex items-center gap-3 max-w-sm">
        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center animate-pulse shrink-0"><i class="ri-heart-fill text-xl"></i></div>
        <div id="toast-content">
            <p class="text-sm font-bold text-slate-800">Sentiste isso?</p>
            <p class="text-xs text-slate-500">Alguém acabou de te enviar um abraço.</p>
        </div>
    </div>

    <div id="crisis-banner" class="fixed inset-x-0 top-20 mx-auto max-w-md z-[100] hidden animate-fade-in-down px-4">
        <div class="bg-rose-600 text-white rounded-2xl shadow-2xl p-4 flex items-start gap-4 border-2 border-white/20 backdrop-blur-xl">
            <div class="bg-white/20 p-2 rounded-full shrink-0"><i class="ri-alarm-warning-fill text-2xl"></i></div>
            <div class="flex-1">
                <h3 class="font-bold text-lg mb-1">Precisas de falar?</h3>
                <p class="text-sm text-rose-100 mb-3">Há ajuda imediata e confidencial disponível.</p>
                <div class="flex gap-2">
                    <a href="tel:213544545" class="bg-white text-rose-600 px-4 py-2 rounded-xl font-bold text-sm flex-1 text-center hover:bg-rose-50 transition-colors">SOS Voz Amiga</a>
                    <button onclick="document.getElementById('crisis-banner').classList.add('hidden')" class="bg-rose-700 text-white px-3 py-2 rounded-xl font-bold text-sm hover:bg-rose-800 transition-colors">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    @if(session('first_visit'))
    <div id="welcome-modal" class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.remove()"></div>
        <div class="bg-white rounded-3xl p-8 max-w-md w-full relative z-10 shadow-2xl animate-fade-up text-center">
            <div class="w-16 h-16 bg-{{ $room->color }}-100 text-{{ $room->color }}-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                <i class="{{ $room->icon }}"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Bem-vindo à {{ $room->name }}</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">Este é um espaço seguro de entreajuda. Aqui podes desabafar, ouvir e ser ouvido sem julgamento.</p>
            <button onclick="document.getElementById('welcome-modal').remove()" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition-colors">Entrar na Roda</button>
        </div>
    </div>
    @endif

    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 z-[80] hidden backdrop-blur-sm lg:hidden transition-opacity" onclick="toggleMobileMenu()"></div>
    <aside id="mobile-drawer" class="fixed inset-y-0 right-0 w-full max-w-[300px] bg-white shadow-2xl z-[90] closed lg:hidden flex flex-col p-6 overflow-y-auto border-l border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">Menu Lumina</h2>
            <button onclick="toggleMobileMenu()" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"><i class="ri-close-line text-xl"></i></button>
        </div>
        <div class="mb-6 bg-slate-50 rounded-2xl p-4 border border-slate-100">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Pessoas Aqui <span id="mobile-drawer-counter" class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-md font-bold text-[10px]">0</span></h3>
            <ul id="users-list-mobile" class="space-y-3 max-h-40 overflow-y-auto pr-1"></ul>
        </div>
        <div class="bg-indigo-900 rounded-3xl p-5 text-white relative overflow-hidden shadow-xl mb-6 ring-1 ring-white/20">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-violet-900"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div><h3 class="font-bold text-lg">Sons de Calma</h3><p id="now-playing-text-mobile" class="text-[10px] text-indigo-200 h-4">Toque para ouvir</p></div><i class="ri-volume-up-line text-indigo-300"></i>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button id="btn-rain-mobile" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 active:bg-white/20 transition-all border border-white/5" onclick="toggleSound('rain')"><i class="ri-rainy-line text-xl"></i> <span class="text-[10px] font-medium">Chuva</span></button>
                    <button id="btn-fire-mobile" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 active:bg-white/20 transition-all border border-white/5" onclick="toggleSound('fire')"><i class="ri-fire-line text-xl"></i> <span class="text-[10px] font-medium">Fogo</span></button>
                    <button id="btn-forest-mobile" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 active:bg-white/20 transition-all border border-white/5" onclick="toggleSound('forest')"><i class="ri-tree-line text-xl"></i> <span class="text-[10px] font-medium">Mata</span></button>
                </div>
            </div>
        </div>
        <div class="mb-6 flex-1">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 px-1">Mudar de Sala</h3>
            <div class="grid grid-cols-4 gap-3">
                @foreach($allRooms as $r)
                <a href="{{ route('chat.show', $r) }}" class="aspect-square rounded-2xl flex items-center justify-center transition-all {{ $r->id == $room->id ? 'bg-'.$r->color.'-100 text-'.$r->color.'-600 ring-2 ring-'.$r->color.'-200' : 'bg-slate-50 text-slate-400 border border-slate-100' }}"><i class="{{ $r->icon }} text-xl"></i></a>
                @endforeach
            </div>
        </div>
    </aside>

    <aside class="hidden lg:flex flex-col w-20 bg-white border-r border-slate-200 items-center py-6 z-20 shrink-0">
        <a href="{{ url('/') }}" class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xl mb-8 shadow-lg shadow-slate-200 hover:scale-110 transition-transform">L</a>
        <div class="space-y-4 flex flex-col w-full px-2">
            @foreach($allRooms as $r)
            <a href="{{ route('chat.show', $r) }}" class="group relative w-12 h-12 mx-auto rounded-2xl flex items-center justify-center transition-all duration-300 {{ $r->id == $room->id ? 'bg-'.$r->color.'-100 text-'.$r->color.'-600 shadow-inner ring-2 ring-'.$r->color.'-50' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                <i class="{{ $r->icon }} text-xl"></i>
                <span class="absolute left-14 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none font-bold shadow-xl">{{ $r->name }}</span>
            </a>
            @endforeach
        </div>
        <div class="mt-auto">
            <a href="{{ url('/dashboard') }}" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors"><i class="ri-user-line"></i></a>
        </div>
    </aside>

    <section class="flex-1 flex flex-col h-full relative min-w-0 bg-white/50">
        
        <header class="glass-panel px-4 py-3 flex flex-col gap-2 z-10 sticky top-0 shrink-0">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3 overflow-hidden">
                    <a href="{{ route('rooms.index') }}" class="lg:hidden w-9 h-9 rounded-full bg-slate-100 active:bg-slate-200 flex items-center justify-center text-slate-500 shrink-0"><i class="ri-arrow-left-line"></i></a>
                    <div class="min-w-0 flex flex-col justify-center">
                        <div class="flex items-center gap-2">
                            <h1 class="text-base md:text-xl font-bold text-slate-900 truncate leading-tight">{{ $room->name }}</h1>
                            <span class="hidden md:flex bg-{{ $room->color }}-100 text-{{ $room->color }}-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md border border-{{ $room->color }}-200 items-center gap-1 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-{{ $room->color }}-500 animate-pulse"></span> LIVE</span>
                        </div>
                        <p class="text-xs text-slate-500 hidden md:block truncate">{{ $room->description }}</p>
                        <p id="now-playing-header" class="text-[10px] text-indigo-500 font-bold md:hidden hidden animate-pulse truncate">🎵 A tocar: Chuva</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 pl-2 shrink-0">
                    <button id="dnd-btn" onclick="toggleDnd()" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-200 text-slate-500 hover:border-indigo-200 hover:text-indigo-600 transition-all text-xs font-bold group" title="Pausar Chat">
                        <i class="ri-pause-circle-line text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="hidden sm:inline" id="dnd-text">Pausa</span>
                    </button>
                    <div class="flex items-center gap-1.5 bg-slate-100/80 px-2.5 py-1.5 rounded-full border border-slate-200/50">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
                        <span id="mobile-counter" class="font-bold text-xs text-slate-700 tabular-nums">0</span>
                    </div>
                    <button onclick="toggleMobileMenu()" class="w-9 h-9 md:hidden rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-200 flex items-center justify-center active:scale-95 transition-transform"><i class="ri-menu-4-line text-lg"></i></button>
                </div>
            </div>

            @if($room->pinned_message || Auth::user()->isModerator())
                <div class="bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-2 flex items-start gap-2 text-xs text-indigo-800">
                    <i class="ri-pushpin-fill text-indigo-500 mt-0.5"></i>
                    <div class="flex-1">
                        @if(Auth::user()->isModerator())
                            <form action="{{ route('chat.pin', $room) }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="message" value="{{ $room->pinned_message }}" placeholder="Fixar mensagem ou regra da sala..." class="w-full bg-transparent border-0 border-b border-indigo-200 p-0 text-xs focus:ring-0 focus:border-indigo-500 placeholder-indigo-300">
                                <button type="submit" class="text-indigo-600 font-bold hover:text-indigo-900">Guardar</button>
                            </form>
                        @else
                            <p class="font-medium">{{ $room->pinned_message }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </header>

        <main id="chat-container" class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 scroll-smooth pb-20 md:pb-8 transition-all duration-500">
            
            <div class="flex justify-center py-6">
                <div class="bg-indigo-50/80 border border-indigo-100 rounded-2xl px-6 py-4 max-w-sm text-center shadow-sm">
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-2"><i class="ri-shield-star-line"></i></div>
                    <p class="text-xs text-indigo-900/80 font-medium leading-relaxed">Bem-vindo a este espaço seguro. Aqui, todas as emoções são válidas. O respeito é a nossa única regra.</p>
                </div>
            </div>

            @if($messages->isEmpty())
            <div id="empty-state" class="flex flex-col items-center justify-center h-full opacity-60 mt-4">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300"><i class="ri-cup-line text-4xl"></i></div>
                <p class="text-slate-500 font-medium">A fogueira está calma.</p>
                <p class="text-xs text-slate-400">Sê o primeiro a partilhar algo hoje.</p>
            </div>
            @endif

            @foreach($messages as $message)
                @php 
                    $isMe = $message->user_id === Auth::id();
                    $hugs = $message->reactions->where('type', 'hug')->count();
                    $candles = $message->reactions->where('type', 'candle')->count();
                    $ears = $message->reactions->where('type', 'ear')->count();
                    
                    // Verifica se a mensagem foi lida por alguém (excluindo o próprio user)
                    $isRead = $message->reads->where('user_id', '!=', Auth::id())->isNotEmpty();
                @endphp

                <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} animate-fade-up group relative" id="msg-{{ $message->id }}">
                    <div class="max-w-[85%] md:max-w-[65%] flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        
                        <div class="relative group/bubble">
                            
                            @if($message->is_sensitive)
                                <div class="sensitive-overlay absolute inset-0 z-20 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-2xl cursor-pointer border border-rose-100" onclick="this.parentElement.classList.add('sensitive-active')">
                                    <span class="text-xs font-bold text-slate-800 bg-white/95 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 border border-slate-100"><i class="ri-eye-off-line text-rose-500"></i> Sensível</span>
                                </div>
                            @endif

                            <div class="{{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-700 rounded-tl-none' }} rounded-2xl shadow-sm px-4 py-3 text-[15px] md:text-base leading-relaxed {{ $message->is_sensitive ? 'blur-content' : '' }}">
                                
                                @if($message->replyTo)
                                    <div class="mb-2 text-xs border-l-2 {{ $isMe ? 'border-indigo-300 bg-indigo-700/30 text-indigo-100' : 'border-indigo-500 bg-slate-50 text-slate-500' }} pl-2 py-1 rounded-r opacity-90 cursor-pointer hover:opacity-100 transition-opacity" onclick="document.getElementById('msg-{{ $message->reply_to_id }}').scrollIntoView({behavior: 'smooth', block: 'center'})">
                                        <span class="font-bold block text-[10px] uppercase tracking-wide opacity-80">
                                            {{ $message->replyTo->user_id == Auth::id() ? 'Ti' : ($message->replyTo->is_anonymous ? 'Anónimo' : $message->replyTo->user->name) }}
                                        </span>
                                        <span class="truncate block max-w-[200px] italic">
                                            {{ Str::limit($message->replyTo->content, 40) }}
                                        </span>
                                    </div>
                                @endif

                                <span class="message-text">{!! nl2br(e($message->content)) !!}</span>
                            </div>

                            <div class="absolute {{ $isMe ? '-left-8' : '-right-8' }} top-2 opacity-0 group-hover/bubble:opacity-100 transition-opacity" x-data="{ open: false }">
                                <button @click="open = !open" class="text-slate-300 hover:text-slate-500 p-1"><i class="ri-more-2-fill"></i></button>
                                
                                <div x-show="open" @click.outside="open = false" style="display: none;" 
                                    class="absolute {{ $isMe ? 'right-0' : 'left-0' }} top-full mt-1 bg-white rounded-lg shadow-xl border border-slate-100 z-50 w-36 overflow-hidden py-1">
                                    
                                    <button onclick="startReply({{ $message->id }}, '{{ $isMe ? 'ti mesmo' : ($message->is_anonymous ? 'Anónimo' : e($message->user->name)) }}', '{{ Str::limit(e($message->content), 30) }}...')" 
                                            class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                                        <i class="ri-reply-line"></i> Responder
                                    </button>

                                    @if(auth()->id() === $message->user_id || auth()->user()->isModerator())
                                        @if(auth()->id() === $message->user_id && $message->created_at->diffInMinutes(now()) <= 5)
                                            <button onclick="startEdit({{ $message->id }}, '{{ e($message->content) }}')" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 flex items-center gap-2">
                                                <i class="ri-pencil-line"></i> Editar
                                            </button>
                                        @endif
                                        <form onsubmit="deleteMessage(event, {{ $message->id }})" class="block">
                                            <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2">
                                                <i class="ri-delete-bin-line"></i> Apagar
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->id() !== $message->user_id)
                                        <button onclick="reportMessage({{ $message->id }})" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                                            <i class="ri-flag-line"></i> Denunciar
                                        </button>
                                        @if(auth()->user()->isModerator())
                                            <div class="h-px bg-slate-100 my-1"></div>
                                            <button onclick="muteUser({{ $message->user_id }})" class="w-full text-left px-3 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 flex items-center gap-2">
                                                <i class="ri-volume-mute-line"></i> Silenciar
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                            
                            @if(!$isMe)
                                <span class="text-[10px] font-bold text-slate-400">{{ $message->is_anonymous ? 'Anónimo' : $message->user->name }}</span>
                            @endif
                            
                            <span class="text-[10px] text-slate-300 flex items-center gap-1 message-meta">
                                @if($message->edited_at) 
                                    <span class="italic text-[9px]">(editado)</span> 
                                @endif
                                {{ $message->created_at->format('H:i') }}
                                
                                @if($isMe)
                                    <span class="read-check ml-0.5 text-xs {{ $isRead ? 'text-blue-500' : 'text-slate-300' }}" title="{{ $isRead ? 'Lido' : 'Enviado' }}">
                                        <i class="{{ $isRead ? 'ri-check-double-line' : 'ri-check-line' }}"></i>
                                    </span>
                                @endif
                            </span>

                            <div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-{{ $isMe ? 'right' : 'left' }}">
                                <button onclick="react({{ $message->id }}, 'hug', this)" class="reaction-btn hover:bg-rose-50 hover:text-rose-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">
                                    <span>🫂</span><span class="count {{ $hugs > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $hugs }}</span>
                                </button>
                                <button onclick="react({{ $message->id }}, 'candle', this)" class="reaction-btn hover:bg-amber-50 hover:text-amber-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">
                                    <span>🕯️</span><span class="count {{ $candles > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $candles }}</span>
                                </button>
                                <button onclick="react({{ $message->id }}, 'ear', this)" class="reaction-btn hover:bg-blue-50 hover:text-blue-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">
                                    <span>👂</span><span class="count {{ $ears > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $ears }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div id="scroll-anchor"></div>
        </main>
        <div class="p-4 bg-white border-t border-slate-100">
            <div id="typing-indicator" class="text-xs text-slate-400 italic h-4 mb-2 transition-opacity opacity-0 pl-4"></div>
            <form id="chat-form" class="relative flex items-end gap-2">
                <div class="flex items-center gap-1 mb-2">
                    <button type="button" id="cw-btn" class="text-slate-400 hover:text-rose-500 p-2 rounded-full hover:bg-slate-50 transition-colors" title="Conteúdo Sensível"><i class="ri-eye-off-line text-lg"></i></button>
                    <div class="relative group" title="Modo Anónimo">
                        <input type="checkbox" id="anonymous-toggle" class="peer sr-only">
                        <label for="anonymous-toggle" class="cursor-pointer text-slate-400 peer-checked:text-indigo-600 p-2 block hover:bg-slate-50 rounded-full"><i class="ri-spy-line text-lg"></i></label>
                    </div>
                </div>
                <textarea id="messageInput" rows="1" class="w-full bg-slate-50 border-0 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all resize-none max-h-32" placeholder="Escreve a tua mensagem..."></textarea>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl transition-all shadow-lg shadow-indigo-200 hover:scale-105 active:scale-95 flex-shrink-0"><i class="ri-send-plane-fill text-xl"></i></button>
            </form>
        </div>
    </section>

    <aside class="hidden xl:flex flex-col w-80 bg-white border-l border-slate-200 p-6 gap-6 z-20 shrink-0 overflow-y-auto">
        <div class="bg-indigo-900 rounded-3xl p-5 text-white relative overflow-hidden shadow-xl group ring-4 ring-slate-50">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-purple-800"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div><h3 class="font-bold text-lg">Sons de Calma</h3><p id="now-playing-text" class="text-[10px] text-indigo-200 h-4 mt-0.5">Pausa para relaxar</p></div><i class="ri-volume-up-line text-indigo-300"></i>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button id="btn-rain" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/5" onclick="toggleSound('rain')"><i class="ri-rainy-line text-xl"></i> <span class="text-[10px] font-medium">Chuva</span></button>
                    <button id="btn-fire" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/5" onclick="toggleSound('fire')"><i class="ri-fire-line text-xl"></i> <span class="text-[10px] font-medium">Lareira</span></button>
                    <button id="btn-forest" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/5" onclick="toggleSound('forest')"><i class="ri-tree-line text-xl"></i> <span class="text-[10px] font-medium">Mata</span></button>
                </div>
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center justify-between"><span>Na Fogueira</span><span id="desktop-counter" class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md font-bold text-xs">0</span></h3>
            <ul id="online-users-list" class="space-y-3"></ul>
        </div>
        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100/60">
            <h4 class="font-bold text-amber-800 text-sm mb-2 flex items-center gap-2"><i class="ri-anchor-line"></i> Grounding</h4>
            <p class="text-xs text-amber-700/70 leading-relaxed font-medium">Se sentires ansiedade:<br>👀 5 coisas que vês<br>✋ 4 coisas que tocas<br>👂 3 coisas que ouves<br>👃 2 coisas que cheiras<br>👅 1 coisa que saboreias</p>
        </div>
    </aside>

    <audio id="audio-rain" loop src="https://cdn.pixabay.com/audio/2022/07/04/audio_06d64d5057.mp3"></audio>
    <audio id="audio-fire" loop src="https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3"></audio>
    <audio id="audio-forest" loop src="https://cdn.pixabay.com/audio/2021/09/06/audio_450d0325b3.mp3"></audio>

<script>
        // CONFIGURAÇÃO E DADOS INICIAIS
        const currentUserId = {{ Auth::id() ?? 'null' }};
        const roomId = {{ $room->id }};
        const isModerator = {{ Auth::user()->isModerator() ? 'true' : 'false' }};
        const followingIds = @json($followingIds ?? []); 
        let isSensitive = false;
        let isDnd = false;
        let typingTimer;
        
        // ESTADO DA MENSAGEM (Novo, Responder, Editar)
        let messageState = 'new'; // valores: 'new', 'reply', 'edit'
        let targetMessageId = null;

        // --- INICIALIZAÇÃO ---
        document.addEventListener('DOMContentLoaded', () => {
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('messageInput');
            const anonymousToggle = document.getElementById('anonymous-toggle');
            const cwBtn = document.getElementById('cw-btn');
            
            scrollToBottom();
            
            // Marcar mensagens como lidas ao entrar ou focar
            markMessagesAsRead();
            window.addEventListener('focus', markMessagesAsRead);

            // Toggle Conteúdo Sensível
            if(cwBtn) {
                cwBtn.addEventListener('click', () => {
                    isSensitive = !isSensitive;
                    if(isSensitive) {
                        cwBtn.classList.replace('text-slate-400', 'text-rose-500');
                        cwBtn.classList.add('bg-rose-50');
                        messageInput.placeholder = "⚠️ Conteúdo Sensível...";
                        messageInput.parentElement.parentElement.classList.add('ring-2', 'ring-rose-200');
                    } else {
                        cwBtn.classList.replace('text-rose-500', 'text-slate-400');
                        cwBtn.classList.remove('bg-rose-50');
                        messageInput.placeholder = "Escreve a tua mensagem...";
                        messageInput.parentElement.parentElement.classList.remove('ring-2', 'ring-rose-200');
                    }
                });
            }

            // Manipulação do Formulário
            if(chatForm) {
                chatForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    await handleMessageSubmit();
                });

                // Atalhos de teclado (Enter para enviar, Esc para cancelar ação)
                messageInput.addEventListener('keydown', (e) => {
                    if(e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleMessageSubmit(); }
                    if(e.key === 'Escape') cancelReplyOrEdit();
                });

                // Whisper de "A escrever..."
                messageInput.addEventListener('input', () => {
                    resizeTextarea(messageInput);
                    if(window.Echo && !isDnd) window.Echo.join(`chat.${roomId}`).whisper('typing', { name: "{{ Auth::user()->name }}" });
                });
            }

            // Iniciar WebSockets
            const waitForEcho = setInterval(() => { 
                if (window.Echo) { clearInterval(waitForEcho); initChatSystem(); } 
            }, 100);
        });

        // --- SISTEMA WEBSOCKETS (REVERB) ---
        function initChatSystem() {
            window.Echo.join(`chat.${roomId}`)
                .here((users) => { updateCounters(users.length); users.forEach(addUserToSidebar); })
                .joining((user) => { addUserToSidebar(user); updateCounters(1, true); if(followingIds.includes(user.id)) showToast(`👋 ${user.name} entrou!`, true); })
                .leaving((user) => { removeUserFromSidebar(user); updateCounters(-1, true); })
                .listen('MessageSent', (e) => {
                    if(isDnd) return;
                    if (e.message.user_id !== currentUserId) {
                        appendMessage(e.message);
                        const indicator = document.getElementById('typing-indicator');
                        if(indicator) indicator.classList.add('opacity-0');
                        markMessagesAsRead(); // Marcar como lido se a janela estiver ativa
                    }
                    scrollToBottom();
                })
                .listen('MessageUpdated', (e) => {
                    updateMessageInDOM(e.message);
                })
                .listen('MessageDeleted', (e) => {
                    const el = document.getElementById(`msg-${e.messageId}`);
                    if(el) { el.classList.add('opacity-0', 'scale-95'); setTimeout(() => el.remove(), 300); }
                })
                .listen('MessageReacted', (e) => {
                    if(isDnd) return;
                    updateReactionUI(e.message_id, e.type, e.count);
                    if (e.message_owner_id === currentUserId && e.action === 'added') triggerSupportEffect(e.type);
                })
                .listen('MessageRead', (e) => {
                    // Atualizar visualmente os ticks de leitura para azul
                    if(e.userId !== currentUserId) { // Se alguém leu as minhas
                        e.messageIds.forEach(id => {
                            const check = document.querySelector(`#msg-${id} .read-check i`);
                            if(check) {
                                check.className = 'ri-check-double-line text-blue-500';
                            }
                        });
                    }
                })
                .listenForWhisper('typing', (e) => { if(!isDnd) showTypingIndicator(e.name); });
        }

        // --- LÓGICA CENTRAL DE ENVIO ---
        async function handleMessageSubmit() {
            const input = document.getElementById('messageInput');
            const content = input.value.trim();
            if (!content) return;

            // Reset UI (Optimistic)
            input.value = ''; resizeTextarea(input); input.focus();
            const currentState = messageState;
            const currentTarget = targetMessageId;
            cancelReplyOrEdit(); 

            try {
                if (currentState === 'edit') {
                    // MODO EDITAR
                    await axios.patch(`/chat/${roomId}/message/${currentTarget}`, { content });
                    
                    // Atualizar DOM local
                    const msgContent = document.querySelector(`#msg-${currentTarget} .message-text`);
                    if(msgContent) msgContent.innerText = content;
                    
                } else {
                    // MODO NOVO / RESPOSTA
                    const payload = { 
                        content, 
                        is_sensitive: isSensitive,
                        is_anonymous: document.getElementById('anonymous-toggle')?.checked || false,
                        reply_to_id: currentState === 'reply' ? currentTarget : null
                    };

                    const response = await axios.post(`/chat/${roomId}/message`, payload);
                    
                    if (response.data.status === 'Message Sent!') {
                        appendMessage(response.data.message); 
                        scrollToBottom();
                    }
                    if(response.data.crisis_detected) document.getElementById('crisis-banner').classList.remove('hidden');
                }
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || "Erro ao processar mensagem.";
                alert(msg);
                input.value = content; // Devolver texto em caso de erro
            }
        }

        // --- FUNÇÕES DE ESTADO (REPLY / EDIT) ---
        
        window.startReply = function(id, name, content) {
            messageState = 'reply';
            targetMessageId = id;
            showInputBar(`A responder a ${name}`, content, 'ri-reply-fill text-indigo-500');
        };

        window.startEdit = function(id, content) {
            messageState = 'edit';
            targetMessageId = id;
            document.getElementById('messageInput').value = content;
            showInputBar(`A editar mensagem`, null, 'ri-pencil-fill text-amber-600', 'amber');
        };

        function showInputBar(title, subtitle, iconClass, color = 'slate') {
            const container = document.getElementById('chat-form').parentElement;
            let bar = document.getElementById('action-bar');
            
            if(!bar) {
                bar = document.createElement('div');
                bar.id = 'action-bar';
                container.insertBefore(bar, document.getElementById('chat-form'));
            }
            
            const bgColor = color === 'amber' ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200';
            bar.className = `flex items-center justify-between ${bgColor} border-t border-l border-r rounded-t-xl px-4 py-2 mb-[-5px] mx-2 relative z-0 text-xs transition-all`;
            
            bar.innerHTML = `
                <div class="flex items-center gap-2 border-l-2 ${color === 'amber' ? 'border-amber-500' : 'border-indigo-500'} pl-2">
                    <i class="${iconClass}"></i>
                    <div>
                        <span class="font-bold ${color === 'amber' ? 'text-amber-700' : 'text-indigo-600'}">${title}</span>
                        ${subtitle ? `<p class="text-slate-500 truncate max-w-[200px]">${subtitle}</p>` : ''}
                    </div>
                </div>
                <button onclick="cancelReplyOrEdit()" class="text-slate-400 hover:text-rose-500"><i class="ri-close-circle-fill text-lg"></i></button>
            `;
            document.getElementById('messageInput').focus();
        }

        window.cancelReplyOrEdit = function() {
            messageState = 'new';
            targetMessageId = null;
            const bar = document.getElementById('action-bar');
            if(bar) bar.remove();
            document.getElementById('messageInput').value = '';
        };

        // --- UI UPDATES E UTILITÁRIOS ---

        function updateMessageInDOM(message) {
            const msgEl = document.getElementById(`msg-${message.id}`);
            if(!msgEl) return;
            const textEl = msgEl.querySelector('.message-text');
            if(textEl) textEl.innerHTML = message.content.replace(/\n/g, '<br>');
            
            // Adicionar flag (editado)
            const metaEl = msgEl.querySelector('.message-meta');
            if(metaEl && !metaEl.innerText.includes('(editado)')) {
                metaEl.innerHTML = `(editado) ` + metaEl.innerHTML;
            }
        }

        async function markMessagesAsRead() {
            if(document.visibilityState === 'visible') {
                await axios.post(`/chat/${roomId}/read`);
            }
        }

        function resizeTextarea(el) {
            el.style.height = 'auto'; el.style.height = (el.scrollHeight) + 'px';
            if(el.value === '') el.style.height = '44px';
        }

        // --- CONSTRUÇÃO DO HTML DA MENSAGEM (APPEND) ---
        function appendMessage(data) {
            const isMe = data.user_id === currentUserId;
            const div = document.createElement('div');
            div.id = `msg-${data.id}`;
            div.className = `flex ${isMe ? 'justify-end' : 'justify-start'} animate-fade-up group mb-4 relative`;

            // HTML da Resposta (Reply)
            let replyHtml = '';
            if (data.reply_to) {
                const replyName = data.reply_to.user_id === currentUserId ? 'Ti' : (data.reply_to.user?.name || 'Alguém');
                replyHtml = `
                    <div class="mb-1 text-xs border-l-2 ${isMe ? 'border-indigo-300 bg-indigo-700/30 text-indigo-100' : 'border-indigo-500 bg-slate-100 text-slate-500'} pl-2 py-1 rounded-r opacity-80 cursor-pointer" onclick="document.getElementById('msg-${data.reply_to_id}').scrollIntoView({behavior: 'smooth', block: 'center'})">
                        <span class="font-bold block text-[10px]">${replyName}</span>
                        <span class="truncate block max-w-[150px]">${data.reply_to.content}</span>
                    </div>
                `;
            }

            // Menu de Opções
            let menuHtml = '';
            // Responder
            menuHtml += `<button onclick="startReply(${data.id}, '${isMe ? 'ti mesmo' : (data.user?.name || 'Alguém')}', '${data.content.substring(0, 30)}...')" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"><i class="ri-reply-line"></i> Responder</button>`;
            
            // Editar/Apagar (Dono/Mod)
            if (isModerator || isMe) {
                menuHtml += `<button onclick="startEdit(${data.id}, '${data.content}')" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 flex items-center gap-2"><i class="ri-pencil-line"></i> Editar</button>`;
                menuHtml += `<form onsubmit="deleteMessage(event, ${data.id})" class="block"><button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2"><i class="ri-delete-bin-line"></i> Apagar</button></form>`;
            }
            // Denunciar/Mute (Outros)
            if (!isMe) {
                menuHtml += `<button onclick="reportMessage(${data.id})" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"><i class="ri-flag-line"></i> Denunciar</button>`;
                if(isModerator) menuHtml += `<div class="h-px bg-slate-100 my-1"></div><button onclick="muteUser(${data.user_id})" class="w-full text-left px-3 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 flex items-center gap-2"><i class="ri-volume-mute-line"></i> Silenciar</button>`;
            }

            // Ticks de Leitura (Checkmarks)
            let readStatusHtml = '';
            if (isMe) {
                readStatusHtml = `<span class="read-check text-slate-300 ml-1 text-xs" title="Lido"><i class="ri-check-line"></i></span>`;
            }

            // Reações
            const reactionBtns = ['hug', 'candle', 'ear'].map(type => {
                const emoji = {'hug':'🫂', 'candle':'🕯️', 'ear':'👂'}[type];
                return `<button onclick="react(${data.id}, '${type}', this)" class="reaction-btn hover:bg-slate-50 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm"><span>${emoji}</span><span class="count hidden font-bold text-[10px]">0</span></button>`;
            }).join('');

            const blurClass = data.is_sensitive ? 'blur-content' : '';
            const overlay = data.is_sensitive ? `<div class="sensitive-overlay absolute inset-0 z-20 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-2xl cursor-pointer border border-rose-100" onclick="this.parentElement.classList.add('sensitive-active')"><span class="text-xs font-bold text-rose-600 flex items-center gap-1.5 bg-rose-50 px-3 py-1.5 rounded-full"><i class="ri-eye-off-line"></i> Conteúdo Sensível</span></div>` : '';

            div.innerHTML = `
                <div class="max-w-[85%] md:max-w-[65%] flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                    <div class="relative group/bubble">
                        <div class="${isMe ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-700 rounded-tl-none'} rounded-2xl shadow-sm px-4 py-3 text-[15px] md:text-base leading-relaxed ${blurClass}">
                            ${replyHtml}
                            <span class="message-text">${data.content}</span>
                        </div>
                        <div class="absolute ${isMe ? '-left-8' : '-right-8'} top-2 opacity-0 group-hover/bubble:opacity-100 transition-opacity" x-data="{ open: false }">
                            <button @click="open = !open" class="text-slate-300 hover:text-slate-500 p-1"><i class="ri-more-2-fill"></i></button>
                            <div x-show="open" @click.outside="open = false" style="display: none;" class="absolute ${isMe ? 'right-0' : 'left-0'} top-full mt-1 bg-white rounded-lg shadow-xl border border-slate-100 z-50 w-32 overflow-hidden py-1">${menuHtml}</div>
                        </div>
                        ${overlay}
                    </div>
                    <div class="flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                        ${!isMe ? `<span class="text-[10px] font-bold text-slate-400">${data.is_anonymous ? 'Anónimo' : (data.user?.name || 'Alguém')}</span>` : ''}
                        <span class="text-[10px] text-slate-300 flex items-center message-meta">
                            Agora ${readStatusHtml}
                        </span>
                        <div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-${isMe ? 'right' : 'left'}">${reactionBtns}</div>
                    </div>
                </div>
            `;
            document.getElementById('chat-messages').appendChild(div);
        }

        // --- FUNÇÕES DE UI E SONS (MANTIDAS) ---
        function toggleSound(type) {
            const allAudios = document.querySelectorAll('audio');
            const targetAudio = document.getElementById(`audio-${type}`);
            const allBtns = document.querySelectorAll('.sound-btn');
            const nowPlayingText = document.getElementById('now-playing-text');
            const nowPlayingTextMobile = document.getElementById('now-playing-text-mobile');
            const nowPlayingHeader = document.getElementById('now-playing-header');
            const soundNames = { 'rain': 'Chuva 🌧️', 'fire': 'Lareira 🔥', 'forest': 'Floresta 🌲' };

            if (!targetAudio.paused) {
                targetAudio.pause();
                allBtns.forEach(btn => btn.classList.remove('active'));
                if(nowPlayingText) nowPlayingText.textContent = "Pausa para relaxar";
                if(nowPlayingTextMobile) nowPlayingTextMobile.textContent = "Toque para ouvir";
                if(nowPlayingHeader) nowPlayingHeader.classList.add('hidden');
            } else {
                allAudios.forEach(a => { a.pause(); a.currentTime = 0; });
                allBtns.forEach(btn => btn.classList.remove('active'));
                targetAudio.volume = 0.5; targetAudio.play();
                const activeBtns = document.querySelectorAll(`#btn-${type}, #btn-${type}-mobile`);
                activeBtns.forEach(btn => btn.classList.add('active'));
                const text = `🎵 A tocar: ${soundNames[type]}`;
                if(nowPlayingText) nowPlayingText.textContent = text;
                if(nowPlayingTextMobile) nowPlayingTextMobile.textContent = text;
                if(nowPlayingHeader) { nowPlayingHeader.textContent = text; nowPlayingHeader.classList.remove('hidden'); }
            }
        }

        function toggleMobileMenu() {
            const drawer = document.getElementById('mobile-drawer');
            const overlay = document.getElementById('mobile-overlay');
            if (drawer.classList.contains('closed')) {
                drawer.classList.remove('closed'); drawer.classList.add('open'); overlay.classList.remove('hidden');
            } else {
                drawer.classList.remove('open'); drawer.classList.add('closed'); overlay.classList.add('hidden');
            }
        }

        function toggleDnd() {
            isDnd = !isDnd;
            const btn = document.getElementById('dnd-btn');
            const txt = document.getElementById('dnd-text');
            const container = document.getElementById('chat-container');
            if(isDnd) {
                btn.classList.replace('bg-white', 'bg-indigo-100'); btn.classList.replace('text-slate-500', 'text-indigo-600'); btn.classList.replace('border-slate-200', 'border-indigo-200');
                txt.textContent = "Em Pausa"; container.classList.add('pause-active');
            } else {
                btn.classList.replace('bg-indigo-100', 'bg-white'); btn.classList.replace('text-indigo-600', 'text-slate-500'); btn.classList.replace('border-indigo-200', 'border-slate-200');
                txt.textContent = "Pausa"; container.classList.remove('pause-active'); scrollToBottom();
            }
        }

        function scrollToBottom() {
            const container = document.getElementById('chat-container');
            if(container) container.scrollTop = container.scrollHeight;
        }

        function updateCounters(val, incremental = false) {
            const els = [document.getElementById('desktop-counter'), document.getElementById('mobile-counter'), document.getElementById('mobile-drawer-counter')];
            let current = parseInt(els[0]?.textContent || 0);
            let final = incremental ? current + val : val;
            final = Math.max(1, final);
            els.forEach(el => { if(el) el.textContent = final; });
        }

        // Funções de Sidebar
        function addUserToSidebar(user) {
            const list = document.getElementById('online-users-list');
            const listMobile = document.getElementById('users-list-mobile');
            if(document.getElementById(`user-online-${user.id}`)) return;

            const isFollowing = followingIds.includes(user.id);
            const bellClass = isFollowing ? 'text-indigo-500 ri-notification-3-fill' : 'text-slate-300 ri-notification-3-line';
            const followBtn = user.id !== currentUserId ? `<button onclick="toggleFollow(${user.id}, this)" class="ml-auto ${bellClass} hover:text-indigo-600 transition-colors"></button>` : '';

            const html = `<div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold border border-indigo-200 shrink-0">${user.name.substring(0, 1)}</div><span class="text-sm font-medium text-slate-600 truncate flex-1">${user.name}</span>${followBtn}`;

            if(list) { const li = document.createElement('li'); li.id = `user-online-${user.id}`; li.className = 'flex items-center gap-2 animate-fade-in mb-2'; li.innerHTML = html; list.appendChild(li); }
            if(listMobile) { const liM = document.createElement('li'); liM.id = `user-mobile-${user.id}`; liM.className = 'flex items-center gap-2 animate-fade-in mb-2'; liM.innerHTML = html; listMobile.appendChild(liM); }
        }

        function removeUserFromSidebar(user) {
            const el = document.getElementById(`user-online-${user.id}`); if (el) el.remove();
            const elM = document.getElementById(`user-mobile-${user.id}`); if (elM) elM.remove();
        }

        window.toggleFollow = async function(targetId, btn) {
            try {
                if(btn.classList.contains('ri-notification-3-line')) {
                    btn.classList.replace('ri-notification-3-line', 'ri-notification-3-fill'); btn.classList.replace('text-slate-300', 'text-indigo-500');
                    if(!followingIds.includes(targetId)) followingIds.push(targetId);
                } else {
                    btn.classList.replace('ri-notification-3-fill', 'ri-notification-3-line'); btn.classList.replace('text-indigo-500', 'text-slate-300');
                    const idx = followingIds.indexOf(targetId); if(idx > -1) followingIds.splice(idx, 1);
                }
                await axios.post(`/chat/${roomId}/follow/${targetId}`);
            } catch(e) { alert("Erro ao seguir."); }
        };

        // Funções de Interação (Report, React, Mute, Delete)
        window.reportMessage = async function(id) { const reason = prompt("Motivo?"); if(reason) { try { await axios.post(`/chat/messages/${id}/report`, { reason }); alert("Obrigado."); } catch(e) { alert("Erro."); } } };
        window.muteUser = async function(id) { if(confirm('Silenciar por 10m?')) try { await axios.post(`/chat/${roomId}/mute/${id}`); alert("Silenciado."); } catch(e) { alert("Erro."); } };
        window.deleteMessage = async function(e, id) { e.preventDefault(); if(confirm('Apagar?')) try { await axios.delete(`/chat/messages/${id}`); } catch(err) { alert('Erro.'); } };
        
        window.react = async function(messageId, type, btnElement) {
            const countSpan = btnElement.querySelector('.count');
            let currentCount = parseInt(countSpan.textContent) || 0;
            countSpan.textContent = currentCount + 1; countSpan.classList.remove('hidden');
            btnElement.classList.add('scale-125', 'bg-indigo-50');
            setTimeout(() => btnElement.classList.remove('scale-125', 'bg-indigo-50'), 200);
            try { await axios.post(`/chat/${roomId}/message/${messageId}/react`, { type: type }); } catch (error) { countSpan.textContent = currentCount; if(currentCount===0) countSpan.classList.add('hidden'); }
        };

        function updateReactionUI(msgId, type, count) {
            const msgEl = document.getElementById(`msg-${msgId}`); if(!msgEl) return;
            const btn = msgEl.querySelector(`button[onclick*="'${type}'"]`);
            if(btn) { const span = btn.querySelector('.count'); span.textContent = count; count > 0 ? span.classList.remove('hidden') : span.classList.add('hidden'); }
        }

        function showTypingIndicator(name) {
            const indicator = document.getElementById('typing-indicator'); if(!indicator) return;
            indicator.innerText = `${name} está a escrever...`; indicator.classList.remove('opacity-0');
            clearTimeout(typingTimer); typingTimer = setTimeout(() => { indicator.classList.add('opacity-0'); }, 3000);
        }

        function triggerSupportEffect(type) {
            if (type === 'hug') { document.body.classList.add('feel-hug-effect'); setTimeout(() => document.body.classList.remove('feel-hug-effect'), 2000); for(let i=0; i<5; i++) setTimeout(() => createFloatingParticle('❤️'), i * 200); showToast("Recebeste um abraço virtual."); }
            else if (type === 'candle') { for(let i=0; i<5; i++) setTimeout(() => createFloatingParticle('✨'), i * 300); showToast("Alguém acendeu uma luz por ti."); }
            else if (type === 'ear') { showToast("Alguém está a ouvir-te."); }
        }

        function createFloatingParticle(emoji) {
            const layer = document.getElementById('visual-effects-layer'); if(!layer) return;
            const el = document.createElement('div'); el.classList.add('floating-heart'); el.innerText = emoji;
            el.style.left = (Math.floor(Math.random() * 80) + 10) + '%'; layer.appendChild(el); setTimeout(() => el.remove(), 3000);
        }

        function showToast(text, isAlert = false) {
            const toast = document.getElementById('support-toast'); if(!toast) return;
            const content = toast.querySelector('div:last-child');
            if(isAlert) content.innerHTML = `<p class="text-sm font-bold text-indigo-800">Alerta de Amigo</p><p class="text-xs text-indigo-600">${text}</p>`;
            else content.innerHTML = `<p class="text-sm font-bold text-slate-800">Sentiste isso?</p><p class="text-xs text-slate-500">${text}</p>`;
            toast.classList.add('active'); setTimeout(() => toast.classList.remove('active'), 4000);
        }
    </script>
    </body>
</html>