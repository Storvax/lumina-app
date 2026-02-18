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
    </style>
</head>
<body class="antialiased text-slate-600 font-sans h-full flex overflow-hidden relative selection:bg-indigo-100 selection:text-indigo-700">

    <div id="visual-effects-layer" class="fixed inset-0 pointer-events-none z-[60]"></div>
    
    <div id="support-toast" class="fixed top-4 inset-x-4 mx-auto md:top-6 md:left-1/2 md:-translate-x-1/2 md:w-auto md:inset-x-auto z-[70] bg-white border border-indigo-100 shadow-2xl rounded-2xl px-4 py-3 flex items-center gap-3 max-w-sm">
        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center animate-pulse shrink-0"><i class="ri-heart-fill text-xl"></i></div>
        <div><p class="text-sm font-bold text-slate-800">Sentiste isso?</p><p class="text-xs text-slate-500">Alguém acabou de te enviar um abraço.</p></div>
    </div>

    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 z-[80] hidden backdrop-blur-sm lg:hidden transition-opacity" onclick="toggleMobileMenu()"></div>
    <aside id="mobile-drawer" class="fixed inset-y-0 right-0 w-full max-w-[300px] bg-white shadow-2xl z-[90] closed lg:hidden flex flex-col p-6 overflow-y-auto border-l border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">Menu Lumina</h2>
            <button onclick="toggleMobileMenu()" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"><i class="ri-close-line text-xl"></i></button>
        </div>

        <div class="mb-6 bg-slate-50 rounded-2xl p-4 border border-slate-100">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center justify-between">
                Pessoas Aqui <span id="mobile-drawer-counter" class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-md font-bold text-[10px]">0</span>
            </h3>
            <div id="users-list-mobile" class="space-y-3 max-h-40 overflow-y-auto pr-1"></div>
        </div>

        <div class="hidden md:flex flex-col w-64 bg-white border-l border-slate-100 p-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Na Fogueira
            </h3>
            <ul id="online-users-list" class="space-y-3 overflow-y-auto flex-1">
                </ul>
        </div>

        <div class="bg-indigo-900 rounded-3xl p-5 text-white relative overflow-hidden shadow-xl mb-6 ring-1 ring-white/20">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-violet-900"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div><h3 class="font-bold text-lg">Sons de Calma</h3><p id="now-playing-text-mobile" class="text-[10px] text-indigo-200 h-4">Toque para ouvir</p></div>
                    <i class="ri-volume-up-line text-indigo-300"></i>
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
         <a href="{{ url('/') }}" class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 text-slate-600 font-bold mt-auto"><i class="ri-arrow-left-circle-line text-xl"></i> Voltar ao Início</a>
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
        <header class="glass-panel px-4 py-3 flex items-center justify-between z-10 sticky top-0 shrink-0 h-16 md:h-auto">
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
                <div class="flex items-center gap-1.5 bg-slate-100/80 px-2.5 py-1.5 rounded-full border border-slate-200/50">
                    <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
                    <span id="mobile-counter" class="font-bold text-xs text-slate-700 tabular-nums">0</span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-9 h-9 md:hidden rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-200 flex items-center justify-center active:scale-95 transition-transform"><i class="ri-menu-4-line text-lg"></i></button>
            </div>
        </header>

        <main id="chat-container" class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 scroll-smooth pb-20 md:pb-8">
            
            <div class="flex justify-center py-6">
                <div class="bg-indigo-50/80 border border-indigo-100 rounded-2xl px-6 py-4 max-w-sm text-center shadow-sm">
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-2"><i class="ri-shield-star-line"></i></div>
                    <p class="text-xs text-indigo-900/80 font-medium leading-relaxed">Bem-vindo a este espaço seguro. Aqui, todas as emoções são válidas. O respeito é a nossa única regra. As conversas antigas dissipam-se como fumo a cada 24h.</p>
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
                @endphp
                <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} animate-fade-up group" id="msg-{{ $message->id }}">
                    <div class="max-w-[85%] md:max-w-[65%] flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="relative {{ $message->is_sensitive ? 'sensitive-container' : '' }}">
                            @if($message->is_sensitive)
                                <div class="sensitive-overlay" onclick="this.parentElement.classList.add('sensitive-active')">
                                    <span class="text-xs font-bold text-slate-800 bg-white/95 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 border border-slate-100"><i class="ri-eye-off-line text-rose-500"></i> Sensível</span>
                                </div>
                            @endif
                            <div class="{{ $isMe ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-none shadow-md shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-700 rounded-2xl rounded-tl-none shadow-sm' }} px-4 py-3 text-[15px] md:text-base leading-relaxed {{ $message->is_sensitive ? 'blur-content' : '' }}">{{ $message->content }}</div>
                        </div>
                        <div class="flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                            @if(!$isMe)<span class="text-[10px] font-bold text-slate-400">{{ $message->user->name }}</span>@endif
                            <span class="text-[10px] text-slate-300">{{ $message->created_at->format('H:i') }}</span>
                            <div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-left">
                                <button onclick="react({{ $message->id }}, 'hug', this)" class="reaction-btn hover:bg-rose-50 hover:text-rose-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">🫂 <span class="count {{ $hugs > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $hugs }}</span></button>
                                <button onclick="react({{ $message->id }}, 'candle', this)" class="reaction-btn hover:bg-amber-50 hover:text-amber-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">🕯️ <span class="count {{ $candles > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $candles }}</span></button>
                                <button onclick="react({{ $message->id }}, 'ear', this)" class="reaction-btn hover:bg-blue-50 hover:text-blue-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">👂 <span class="count {{ $ears > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $ears }}</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div id="scroll-anchor"></div>
        </main>

        <div id="typing-indicator" class="text-xs text-slate-400 italic h-4 mb-2 transition-opacity opacity-0 pl-4">
        </div>
        <footer class="p-3 md:p-6 bg-white/0 shrink-0">
            <form id="chat-form" class="max-w-4xl mx-auto relative flex items-end gap-2 md:gap-3 glass-panel p-1.5 md:p-2 rounded-[24px] md:rounded-[2rem] shadow-xl shadow-indigo-100/50 border border-white/60">
                <button type="button" id="cw-btn" class="mb-1 p-2.5 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Conteúdo Sensível"><i class="ri-eye-off-line text-lg md:text-xl"></i></button>
                <div class="flex-1">
                    <textarea id="message-input" rows="1" placeholder="Escreve aqui..." class="w-full bg-transparent border-0 focus:ring-0 text-[15px] md:text-base py-3 max-h-32 resize-none text-slate-700 placeholder:text-slate-400/80" style="min-height: 44px;"></textarea>
                </div>
                <button type="submit" class="mb-1 w-10 h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center shadow-lg shadow-indigo-500/30 transition-transform active:scale-95"><i class="ri-send-plane-fill"></i></button>
            </form>
        </footer>
    </section>

    <aside class="hidden xl:flex flex-col w-80 bg-white border-l border-slate-200 p-6 gap-6 z-20 shrink-0 overflow-y-auto">
        <div class="bg-indigo-900 rounded-3xl p-5 text-white relative overflow-hidden shadow-xl group ring-4 ring-slate-50">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-purple-800"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div><h3 class="font-bold text-lg">Sons de Calma</h3><p id="now-playing-text" class="text-[10px] text-indigo-200 h-4 mt-0.5">Pausa para relaxar</p></div>
                    <i class="ri-volume-up-line text-indigo-300"></i>
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
            <div id="users-list-desktop" class="space-y-3"></div>
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
        const currentUserId = {{ Auth::id() ?? 'null' }};
        const roomId = {{ $room->id }};
        let isSensitive = false;

        // NÃO PRECISAMOS DE addWelcomeMessage() POR JAVASCRIPT
        // O aviso agora está fixo no HTML (primeiro elemento do scroll)

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
                const activeBtnDesktop = document.getElementById(`btn-${type}`);
                const activeBtnMobile = document.getElementById(`btn-${type}-mobile`);
                if(activeBtnDesktop) activeBtnDesktop.classList.add('active');
                if(activeBtnMobile) activeBtnMobile.classList.add('active');
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

        document.addEventListener('DOMContentLoaded', () => {
            const chatContainer = document.getElementById('chat-container');
            const messageInput = document.getElementById('message-input');
            const mobileCounter = document.getElementById('mobile-counter');
            const desktopCounter = document.getElementById('desktop-counter');
            const mobileDrawerCounter = document.getElementById('mobile-drawer-counter');
            const usersListDesktop = document.getElementById('users-list-desktop');
            const usersListMobile = document.getElementById('users-list-mobile');
            const typingIndicator = document.getElementById('typing-indicator');
            const cwBtn = document.getElementById('cw-btn');
            
            if(chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

            if(cwBtn) {
                cwBtn.addEventListener('click', () => {
                    isSensitive = !isSensitive;
                    if(isSensitive) {
                        cwBtn.classList.add('text-rose-500', 'bg-rose-50'); cwBtn.classList.remove('text-slate-400');
                        messageInput.placeholder = "⚠️ Conteúdo Sensível...";
                        messageInput.parentElement.parentElement.classList.add('ring-2', 'ring-rose-200');
                    } else {
                        cwBtn.classList.remove('text-rose-500', 'bg-rose-50'); cwBtn.classList.add('text-slate-400');
                        messageInput.placeholder = "Escreve aqui...";
                        messageInput.parentElement.parentElement.classList.remove('ring-2', 'ring-rose-200');
                    }
                });
            }

            const waitForEcho = setInterval(() => { if (window.Echo) { clearInterval(waitForEcho); initChatSystem(); } }, 100);

            function initChatSystem() {
                window.Echo.join(`chat.${roomId}`)
                    .here((users) => {
                        // Lista inicial
                        users.forEach(user => addUserToSidebar(user));
                    })
                    .joining((user) => {
                        addUserToSidebar(user);
                        console.log(user.name + ' entrou.');
                    })
                    .leaving((user) => {
                        removeUserFromSidebar(user);
                        console.log(user.name + ' saiu.');
                    })
                    .listen('MessageSent', (e) => {
                        const emptyState = document.getElementById('empty-state');
                        if(emptyState) emptyState.remove();
                        if (e.user_id !== currentUserId) appendMessage(e);
                        if(typingIndicator) typingIndicator.style.opacity = '0';
                    })
                    .listen('MessageReacted', (e) => {
                        updateReactionUI(e.message_id, e.type, e.count);
                        if (e.message_owner_id === currentUserId && e.action === 'added') triggerSupportEffect(e.type);
                    })
                    .listenForWhisper('typing', (e) => {
                        showTypingIndicator(e.name);
                    });
            }

            // Funções Auxiliares de UI
            function addUserToSidebar(user) {
                const list = document.getElementById('online-users-list');
                if(document.getElementById(`user-${user.id}`)) return; // Já existe

                const li = document.createElement('li');
                li.id = `user-${user.id}`;
                li.className = 'flex items-center gap-3 animate-fade-in';
                li.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold border border-indigo-200">
                        ${user.name.substring(0, 1)}
                    </div>
                    <span class="text-sm font-medium text-slate-600 truncate">${user.name}</span>
                `;
                list.appendChild(li);
            }

            function removeUserFromSidebar(user) {
                const el = document.getElementById(`user-${user.id}`);
                if (el) el.remove();
            }

            function updateCounter(val, incremental = false) {
                // Lógica de delay reduzida
                let current = parseInt(desktopCounter ? desktopCounter.textContent : 0) || 0;
                let final = incremental ? current + val : val;
                final = Math.max(1, final); // Nunca menos que 1
                if(mobileCounter) mobileCounter.textContent = final;
                if(desktopCounter) desktopCounter.textContent = final;
                if(mobileDrawerCounter) mobileDrawerCounter.textContent = final;
            }

            function updateUserList(users) {
                if(usersListDesktop) usersListDesktop.innerHTML = '';
                if(usersListMobile) usersListMobile.innerHTML = '';
                users.forEach(u => addUserToList(u));
            }

            function addUserToList(user) {
                const html = `<div class="relative"><div class="w-8 h-8 rounded-full bg-{{ $room->color }}-100 flex items-center justify-center text-{{ $room->color }}-600 font-bold text-xs shadow-sm">${user.name.charAt(0)}</div><div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></div></div><span class="text-sm font-medium text-slate-600 truncate">${user.name}</span>`;
                
                // Adicionar a Desktop
                if(usersListDesktop && !document.getElementById(`user-desktop-${user.id}`)) {
                    const div = document.createElement('div');
                    div.id = `user-desktop-${user.id}`;
                    div.className = "flex items-center gap-3 animate-fade-up";
                    div.innerHTML = html;
                    usersListDesktop.appendChild(div);
                }
                // Adicionar a Mobile
                if(usersListMobile && !document.getElementById(`user-mobile-${user.id}`)) {
                    const div = document.createElement('div');
                    div.id = `user-mobile-${user.id}`;
                    div.className = "flex items-center gap-3 animate-fade-up";
                    div.innerHTML = html;
                    usersListMobile.appendChild(div);
                }
            }

            function removeUserFromList(user) {
                const elD = document.getElementById(`user-desktop-${user.id}`);
                const elM = document.getElementById(`user-mobile-${user.id}`);
                if(elD) elD.remove();
                if(elM) elM.remove();
            }

            // Lógica do ENTER para enviar
            if(messageInput) {
                // Auto-resize
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px';
                    if(this.value === '') this.style.height = '44px';
                    if(window.Echo) window.Echo.join(`chat.${roomId}`).whisper('typing', { name: 'Alguém' });
                });

                // Keydown para Enter
                messageInput.addEventListener('keydown', function(e) {
                    // Se for Enter e NÃO for Shift (Shift+Enter faz nova linha)
                    if(e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault(); // Previne a quebra de linha padrão
                        document.getElementById('chat-form').dispatchEvent(new Event('submit')); // Dispara o envio
                    }
                });
            }

            const chatForm = document.getElementById('chat-form');
            if(chatForm) {
                chatForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const content = messageInput.value.trim();
                    if (!content) return;
                    const emptyState = document.getElementById('empty-state');
                    if(emptyState) emptyState.remove();
                    messageInput.value = ''; messageInput.style.height = '44px'; messageInput.focus();
                    const wasSensitive = isSensitive;
                    if(isSensitive && cwBtn) cwBtn.click();
                    appendMessage({ content: content, user_id: currentUserId, created_at: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), is_sensitive: wasSensitive });
                    try { await axios.post(`/sala/${roomId}/send`, { content: content, is_sensitive: wasSensitive }); } catch (error) { console.error(error); }
                });
            }
        });

        // (Restante código igual: window.react, updateReactionUI, triggerSupportEffect, createFloatingParticle, showToast, appendMessage, escapeHtml)
        window.react = async function(messageId, type, btnElement) {
            const countSpan = btnElement.querySelector('.count');
            let currentCount = parseInt(countSpan.textContent) || 0;
            countSpan.textContent = currentCount + 1;
            countSpan.classList.remove('hidden');
            btnElement.classList.add('scale-125', 'bg-indigo-50');
            setTimeout(() => btnElement.classList.remove('scale-125', 'bg-indigo-50'), 200);
            try { await axios.post(`/sala/${roomId}/message/${messageId}/react`, { type: type }); } 
            catch (error) { countSpan.textContent = currentCount; if(currentCount===0) countSpan.classList.add('hidden'); }
        };

        function updateReactionUI(msgId, type, count) {
            const msgEl = document.getElementById(`msg-${msgId}`);
            if(!msgEl) return;
            const btns = msgEl.querySelectorAll('.reaction-btn');
            btns.forEach(btn => {
                if(btn.getAttribute('onclick').includes(`'${type}'`)) {
                    const span = btn.querySelector('.count');
                    span.textContent = count;
                    count > 0 ? span.classList.remove('hidden') : span.classList.add('hidden');
                }
            });
        }

        function triggerSupportEffect(type) {
            const body = document.body;
            if (type === 'hug') {
                body.classList.add('feel-hug-effect');
                setTimeout(() => body.classList.remove('feel-hug-effect'), 2000);
                for(let i=0; i<5; i++) setTimeout(() => createFloatingParticle('❤️'), i * 200);
                showToast("Alguém enviou-te um abraço virtual.");
            } else if (type === 'candle') {
                for(let i=0; i<5; i++) setTimeout(() => createFloatingParticle('✨'), i * 300);
                showToast("Alguém acendeu uma luz por ti.");
            } else if (type === 'ear') {
                showToast("Alguém está a ouvir-te atentamente.");
            }
        }

        function createFloatingParticle(emoji) {
            const layer = document.getElementById('visual-effects-layer');
            if(!layer) return;
            const el = document.createElement('div');
            el.classList.add('floating-heart');
            el.innerText = emoji;
            el.style.left = (Math.floor(Math.random() * 80) + 10) + '%';
            layer.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }

        function showToast(text) {
            const toast = document.getElementById('support-toast');
            if(!toast) return;
            toast.querySelector('p:last-child').textContent = text;
            toast.classList.add('active');
            setTimeout(() => toast.classList.remove('active'), 4000);
        }

        function appendMessage(data) {
            const isMe = data.user_id === currentUserId;
            const div = document.createElement('div');
            div.id = `msg-${data.id || 'temp-' + Date.now()}`;
            
            let contentHtml = escapeHtml(data.content);
            let blurClass = data.is_sensitive ? 'blur-content' : '';
            let overlayHtml = data.is_sensitive ? 
                `<div class="sensitive-overlay" onclick="this.parentElement.classList.add('sensitive-active')"><span class="text-xs font-bold text-slate-800 bg-white/95 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 border border-slate-100"><i class="ri-eye-off-line text-rose-500"></i> Sensível</span></div>` : '';

            const reactionBtns = `
                <button onclick="react(${data.id}, 'hug', this)" class="reaction-btn hover:bg-rose-50 hover:text-rose-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">🫂 <span class="count hidden font-bold text-[10px]">0</span></button>
                <button onclick="react(${data.id}, 'candle', this)" class="reaction-btn hover:bg-amber-50 hover:text-amber-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">🕯️ <span class="count hidden font-bold text-[10px]">0</span></button>
                <button onclick="react(${data.id}, 'ear', this)" class="reaction-btn hover:bg-blue-50 hover:text-blue-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm">👂 <span class="count hidden font-bold text-[10px]">0</span></button>
            `;

            if (isMe) {
                div.className = "flex justify-end animate-fade-up group mb-4";
                div.innerHTML = `<div class="max-w-[85%] md:max-w-[65%] flex flex-col items-end"><div class="relative ${data.is_sensitive ? 'sensitive-container' : ''}">${overlayHtml}<div class="bg-indigo-600 text-white rounded-2xl rounded-tr-none shadow-md shadow-indigo-100 px-4 py-3 text-[15px] md:text-base leading-relaxed ${blurClass}">${contentHtml}</div></div><div class="flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity"><span class="text-[10px] text-slate-400">${data.created_at}</span><div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-right">${reactionBtns}</div></div></div>`;
            } else {
                div.className = "flex justify-start animate-fade-up group mb-4";
                div.innerHTML = `<div class="max-w-[85%] md:max-w-[65%] flex flex-col items-start"><div class="relative ${data.is_sensitive ? 'sensitive-container' : ''}">${overlayHtml}<div class="bg-white border border-slate-200 text-slate-700 rounded-2xl rounded-tl-none shadow-sm px-4 py-3 text-[15px] md:text-base leading-relaxed ${blurClass}">${contentHtml}</div></div><div class="flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity"><span class="text-[10px] font-bold text-slate-400">${data.user_name || 'Alguém'}</span><span class="text-[10px] text-slate-300">${data.created_at}</span><div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-left">${reactionBtns}</div></div></div>`;
            }

            document.getElementById('scroll-anchor').before(div);
            const container = document.getElementById('chat-container');
            if(container.scrollHeight - container.scrollTop < 1000) container.scrollTop = container.scrollHeight;
        }

        function escapeHtml(text) { return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"); }

        let typingTimer;
        const messageInput = document.getElementById('messageInput'); // O teu <input> ou <textarea>

        messageInput.addEventListener('input', () => {
            window.Echo.join(`chat.${roomId}`)
                .whisper('typing', {
                    name: "{{ Auth::user()->name }}"
                });
        });

        function showTypingIndicator(name) {
            const indicator = document.getElementById('typing-indicator');
            indicator.innerText = `${name} está a escrever...`;
            indicator.classList.remove('opacity-0');
            
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                indicator.classList.add('opacity-0');
            }, 3000); // 3 segundos de debounce
        }
    </script>
</body>
</html>