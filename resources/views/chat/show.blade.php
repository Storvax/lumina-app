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
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 4px; }
    </style>
</head>
<body class="antialiased text-slate-600 font-sans h-full flex overflow-hidden relative selection:bg-indigo-100 selection:text-indigo-700">

    <div id="visual-effects-layer" class="fixed inset-0 pointer-events-none z-[60]"></div>
    
    <div id="support-toast" class="fixed top-4 inset-x-4 mx-auto md:top-6 md:left-1/2 md:-translate-x-1/2 md:w-auto md:inset-x-auto z-[70] bg-white border border-indigo-100 shadow-2xl rounded-2xl px-4 py-3 flex items-center gap-3 max-w-sm">
        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center animate-pulse shrink-0"><i class="ri-heart-fill text-xl"></i></div>
        <div id="toast-content">
            <p class="text-sm font-bold text-slate-800">Nova Notificação</p>
            <p class="text-xs text-slate-500">Alguém interagiu contigo.</p>
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
            <div class="w-16 h-16 bg-{{ $room->color }}-100 text-{{ $room->color }}-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"><i class="{{ $room->icon }}"></i></div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Bem-vindo à {{ $room->name }}</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">Este é um espaço seguro de entreajuda. Aqui podes desabafar, ouvir e ser ouvido sem julgamento.</p>
            <button onclick="document.getElementById('welcome-modal').remove()" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition-colors">Entrar na Roda</button>
        </div>
    </div>
    @endif

    @include('chat.partials.mobile-drawer')
    
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
        @include('chat.partials.header')
        @include('chat.partials.messages-area')
    </section>

    @include('chat.partials.sidebar')

    <audio id="audio-rain" loop src="https://cdn.pixabay.com/audio/2022/07/04/audio_06d64d5057.mp3"></audio>
    <audio id="audio-fire" loop src="https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3"></audio>
    <audio id="audio-forest" loop src="https://cdn.pixabay.com/audio/2021/09/06/audio_450d0325b3.mp3"></audio>

    @include('chat.partials.scripts')
</body>
</html>