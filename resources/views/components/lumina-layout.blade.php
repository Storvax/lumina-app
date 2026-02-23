<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      class="
        {{ auth()->check() && auth()->user()->a11y_text_size !== 'base' ? 'text-'.auth()->user()->a11y_text_size : 'text-base' }}
        {{ auth()->check() && auth()->user()->a11y_dyslexic_font ? 'font-dyslexic' : '' }}
        {{ auth()->check() && auth()->user()->a11y_reduced_motion ? 'reduced-motion' : '' }}
      ">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Lumina' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .mesh-gradient { background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.1) 0%, rgba(255, 255, 255, 0) 50%), radial-gradient(circle at 100% 0%, rgba(20, 184, 166, 0.1) 0%, rgba(255, 255, 255, 0) 50%); }
        .animate-fade-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        /* O FILTRO DE TEMPERATURA (F.lux effect) */
        #night-mode-filter {
            background-color: #ff9900;
            mix-blend-mode: multiply;
            opacity: 0;
            transition: opacity 2s ease-in-out;
            pointer-events: none;
            z-index: 9999;
        }
        
        /* --- MODO ALTO CONTRASTE --- */
        body.high-contrast { background-color: #ffffff !important; color: #000000 !important; }
        body.high-contrast .glass-card, body.high-contrast .glass, body.high-contrast nav .glass { background: #ffffff !important; backdrop-filter: none !important; border: 2px solid #000000 !important; box-shadow: none !important; }
        body.high-contrast .text-slate-400, body.high-contrast .text-slate-500, body.high-contrast .text-slate-600 { color: #000000 !important; }
        body.high-contrast button, body.high-contrast a { text-decoration: underline; font-weight: 700 !important; }
        :focus-visible { outline: 3px solid #000000 !important; outline-offset: 2px; }

        /* --- MICROINTERAÇÕES TERAPÊUTICAS --- */
        .wave-effect { position: relative; overflow: hidden; }
        .wave-effect::after { 
            content: ''; position: absolute; top: 50%; left: 50%; width: 100%; height: 100%; 
            background: currentColor; border-radius: 50%; transform: translate(-50%, -50%) scale(0); 
            opacity: 0.2; pointer-events: none; 
        }
        .wave-effect.active::after { animation: expandWave 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; }
        @keyframes expandWave { 
            0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.3; } 
            100% { transform: translate(-50%, -50%) scale(4); opacity: 0; } 
        }

        {{ $css ?? '' }}
    </style>
</head>
<body class="antialiased text-slate-600 bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white relative flex flex-col min-h-screen">

    <div id="calm-loader" class="fixed inset-0 z-[9999] bg-slate-50/90 backdrop-blur-md flex flex-col items-center justify-center transition-opacity duration-700 opacity-0 pointer-events-none">
        <div class="relative w-24 h-24 flex items-center justify-center">
            <div class="absolute inset-0 border-2 border-indigo-200 rounded-full animate-[ping_4s_cubic-bezier(0,0,0.2,1)_infinite]"></div>
            <div class="absolute inset-4 border-2 border-indigo-300 rounded-full animate-[ping_4s_cubic-bezier(0,0,0.2,1)_infinite]" style="animation-delay: 1s;"></div>
            <i class="ri-leaf-line text-3xl text-indigo-500 animate-pulse"></i>
        </div>
        <p class="mt-8 text-xs font-bold text-indigo-400 tracking-widest uppercase animate-pulse">Respira...</p>
    </div>

    <div id="night-mode-filter" class="fixed inset-0 w-full h-full"></div>

    <div id="sosModal" class="fixed inset-0 z-[100] hidden">
        <div id="modalOverlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity cursor-pointer"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4 animate-fade-up">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-rose-100">
                <div class="bg-rose-50 p-6 text-center border-b border-rose-100">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-500 text-3xl">
                        <i class="ri-alarm-warning-fill"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">Ajuda Imediata</h3>
                    <p class="text-slate-600 mt-2 text-sm">Não estás sozinho. Estas linhas estão disponíveis agora.</p>
                </div>
                <div class="p-6 space-y-4">
                    <a href="tel:112" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-rose-50 hover:border-rose-200 transition-colors group">
                        <div class="flex items-center gap-4">
                            <span class="text-2xl font-black text-slate-800 group-hover:text-rose-600">112</span>
                            <div class="text-left"><p class="font-bold text-slate-800">Emergência Nacional</p><p class="text-xs text-slate-500">Risco de vida iminente</p></div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-rose-500 shadow-sm"><i class="ri-phone-fill"></i></div>
                    </a>
                    <a href="tel:808242424" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-50 hover:border-blue-200 transition-colors group">
                        <div class="flex items-center gap-4">
                            <span class="text-xl font-bold text-slate-800 group-hover:text-blue-600">SNS 24</span>
                            <div class="text-left"><p class="font-bold text-slate-800">Apoio Psicológico</p><p class="text-xs text-slate-500">Disponível 24h por dia</p></div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-500 shadow-sm"><i class="ri-phone-fill"></i></div>
                    </a>
                </div>
                <div class="bg-slate-50 p-4 text-center">
                    <button id="modalClose" class="text-slate-500 font-semibold hover:text-slate-800 text-sm">Cancelar / Voltar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="globalAlertModal" class="fixed inset-0 z-[150] hidden" role="dialog" aria-modal="true" aria-labelledby="globalAlertTitle">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeAlert()"></div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none p-4">
            <div id="globalAlertPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm pointer-events-auto p-6 text-center border-2 border-transparent high-contrast:border-black transform transition-all scale-100">
                <div id="globalAlertIcon" class="mb-4 text-4xl"></div>
                <h3 id="globalAlertTitle" class="text-xl font-bold text-slate-900 mb-2"></h3>
                <p id="globalAlertMessage" class="text-slate-600 mb-6 text-sm"></p>
                <button id="globalAlertBtn" onclick="closeAlert()" class="close-modal-btn w-full py-3 rounded-xl font-bold text-white shadow-lg transition-all active:scale-95">Entendido</button>
            </div>
        </div>
    </div>

    @if(isset($header))
        {{ $header }}
    @else
        <nav class="fixed top-0 w-full z-50 transition-all duration-300">
            <div class="glass max-w-6xl mx-auto mt-4 md:rounded-full rounded-2xl px-6 py-3 flex justify-between items-center shadow-lg shadow-black/5 mx-4 md:mx-auto">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-500 to-violet-400 flex items-center justify-center text-white font-bold text-lg group-hover:rotate-12 transition-transform">L</div>
                    <span class="text-xl font-bold text-slate-800 tracking-tight">Lumina<span class="text-indigo-500">.</span></span>
                </a>

                <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-bold' : '' }}">Dashboard</a>
                    @else
                        <a href="{{ url('/#inicio') }}" class="text-slate-600 hover:text-indigo-600 transition-colors">Início</a>
                    @endauth
                    <a href="{{ route('forum.index') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('forum.*') ? 'text-indigo-600 font-bold' : '' }}">Mural</a>
                    <a href="{{ route('rooms.index') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('rooms.*') ? 'text-indigo-600 font-bold' : '' }}">Fogueira</a>
                    <a href="{{ route('calm.index') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('calm.*') ? 'text-indigo-600 font-bold' : '' }}">Zona Calma</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <div class="relative" x-data="{ open: false, count: {{ Auth::user()->unreadNotifications->count() }} }" x-on:new-notification.window="count++">
                            <button @click="open = !open; if(open) { axios.post('{{ route('notifications.read') }}'); count = 0; }" 
                                    class="relative w-9 h-9 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                <i class="ri-notification-3-line"></i>
                                <span x-show="count > 0" x-text="count" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white animate-pulse"></span>
                            </button>

                            <div x-show="open" @click.outside="open = false" 
                                 x-transition:enter="transition ease-out duration-200 opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute right-0 top-full mt-3 w-80 max-w-[90vw] bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden py-2" style="display: none;">
                                <div class="px-4 py-2 border-b border-slate-50 flex justify-between items-center">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Notificações</h3>
                                    <button @click="open = false" class="text-slate-300 hover:text-slate-500"><i class="ri-close-line"></i></button>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    @forelse(Auth::user()->notifications()->latest()->take(8)->get() as $notification)
                                        @php $data = $notification->data; @endphp
                                        <a href="{{ isset($data['post_id']) ? route('forum.show', $data['post_id']) : '#' }}" class="block px-4 py-3 hover:bg-slate-50 transition-colors {{ $notification->read_at ? 'opacity-60' : 'bg-indigo-50/20' }}">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-full {{ ($data['type'] ?? '') == 'reaction' ? 'bg-rose-100 text-rose-500' : 'bg-indigo-100 text-indigo-500' }} flex items-center justify-center text-sm shrink-0">
                                                    <i class="{{ ($data['type'] ?? '') == 'reaction' ? 'ri-heart-fill' : 'ri-chat-1-fill' }}"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-700">{{ $data['message'] ?? 'Nova interação' }}</p>
                                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="px-4 py-8 text-center text-slate-400 text-xs">Sem notificações. O silêncio também é bom. 🍃</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('profile.show') }}" class="hidden md:flex text-sm font-semibold text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-full transition-colors border border-transparent hover:border-indigo-100">Perfil</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-full transition-colors">Entrar</a>
                    @endauth

                    <button id="sosBtnTrigger" class="bg-white border border-rose-100 text-rose-500 hover:bg-rose-50 hover:border-rose-200 px-3 md:px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                        </span>
                        SOS
                    </button>
                    </div>
            </div>
        </nav>
    @endif

    <div class="fixed top-0 left-0 w-full h-full mesh-gradient opacity-60 -z-10 pointer-events-none"></div>
    
    <main class="flex-1 w-full pt-32 pb-24 md:pb-12">
        {{ $slot }}
    </main>

    @auth
        @php
            // Lógica para detetar se o utilizador está ansioso/sobrecarregado
            $tags = Auth::user()->emotional_tags ?? [];
            $needsCalm = in_array('Ansiedade', $tags) || in_array('Sobrecarregado(a)', $tags);
        @endphp

        <div class="md:hidden fixed bottom-0 left-0 w-full z-40 bg-white/90 backdrop-blur-xl border-t border-slate-100 pb-safe shadow-[0_-10px_40px_rgba(0,0,0,0.05)] transition-all">
            <div class="flex justify-around items-center h-[70px] px-2 pb-2">
                
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-indigo-600 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : '' }}">
                    <i class="ri-home-smile-2-{{ request()->routeIs('dashboard') ? 'fill' : 'line' }} text-2xl"></i>
                    <span class="text-[9px] font-bold">Início</span>
                </a>
                
                <a href="{{ route('forum.index') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-indigo-600 {{ request()->routeIs('forum.*') ? 'text-indigo-600' : '' }}">
                    <i class="ri-quill-pen-{{ request()->routeIs('forum.*') ? 'fill' : 'line' }} text-2xl"></i>
                    <span class="text-[9px] font-bold">Mural</span>
                </a>
                
                @if($needsCalm)
                    <a href="{{ route('calm.index') }}" class="flex flex-col items-center justify-center w-14 h-14 -mt-8 bg-teal-500 text-white rounded-full shadow-lg shadow-teal-500/30 ring-4 ring-white animate-[pulse_4s_ease-in-out_infinite]">
                        <i class="ri-lungs-fill text-2xl"></i>
                    </a>
                @else
                    <a href="{{ route('rooms.index') }}" class="flex flex-col items-center justify-center w-14 h-14 -mt-8 bg-orange-500 text-white rounded-full shadow-lg shadow-orange-500/30 ring-4 ring-white">
                        <i class="ri-fire-fill text-2xl"></i>
                    </a>
                @endif
                
                @if($needsCalm)
                    <a href="{{ route('rooms.index') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-orange-500 {{ request()->routeIs('rooms.*') ? 'text-orange-500' : '' }}">
                        <i class="ri-fire-{{ request()->routeIs('rooms.*') ? 'fill' : 'line' }} text-2xl"></i>
                        <span class="text-[9px] font-bold">Fogueira</span>
                    </a>
                @else
                    <a href="{{ route('calm.index') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-teal-500 {{ request()->routeIs('calm.*') ? 'text-teal-500' : '' }}">
                        <i class="ri-leaf-{{ request()->routeIs('calm.*') ? 'fill' : 'line' }} text-2xl"></i>
                        <span class="text-[9px] font-bold">Calma</span>
                    </a>
                @endif

                <a href="{{ route('profile.show') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-indigo-600 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : '' }}">
                    <i class="ri-user-smile-{{ request()->routeIs('profile.*') ? 'fill' : 'line' }} text-2xl"></i>
                    <span class="text-[9px] font-bold">Perfil</span>
                </a>
            </div>
        </div>
    @endauth

    <footer class="bg-white border-t border-slate-100 pt-20 pb-28 md:pb-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1 space-y-4">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-lg">L</div>
                        <span class="text-xl font-bold text-slate-900">Lumina.</span>
                    </a>
                    <p class="text-sm text-slate-500 leading-relaxed">Democratizar o acesso ao bem-estar mental em Portugal, criando pontes entre pessoas e profissionais.</p>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-6">Plataforma</h4>
                    <ul class="space-y-3 text-sm text-slate-500">
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-indigo-600 transition-colors">A Fogueira</a></li>
                        <li><a href="{{ route('forum.index') }}" class="hover:text-indigo-600 transition-colors">Mural</a></li>
                        <li><a href="{{ route('calm.index') }}" class="hover:text-indigo-600 transition-colors">Zona Calma</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-6">Legal</h4>
                    <ul class="space-y-3 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Termos de Uso</a></li>
                        <li><a href="{{ route('privacy.index') }}" class="hover:text-indigo-600 transition-colors">Privacidade e Dados</a></li>
                    </ul>
                </div>
                <div>
                    <div class="bg-amber-50 border border-amber-100 p-5 rounded-2xl">
                        <p class="text-xs font-bold text-amber-700 uppercase mb-2 flex items-center gap-1"><i class="ri-alert-line"></i> Importante</p>
                        <p class="text-xs text-amber-800/80 leading-relaxed">A Lumina não presta atos médicos. Em emergência liga <span class="font-bold">112</span> ou <span class="font-bold">SNS24 (808 24 24 24)</span>.</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-center">
                <p class="text-xs text-slate-400">© {{ date('Y') }} Lumina Portugal. Todos os direitos reservados.</p>
                <p class="text-xs text-slate-400 flex items-center gap-1">Feito com <i class="ri-heart-fill text-rose-400"></i> e empatia.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- 🌙 MODO NOTURNO (TEMPERATURA) F.lux Effect ---
            // Aplica um filtro de redução de luz azul entre as 21:00 e as 06:00
            const hour = new Date().getHours();
            if (hour >= 21 || hour < 6) {
                const filter = document.getElementById('night-mode-filter');
                filter.style.opacity = '0.07'; // Um toque super subtil de sépia
            }

            // --- GESTOR DE ACESSIBILIDADE LUMINA ---
            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('class', 'sr-only');
            document.body.appendChild(announcer);

            window.announce = function(message) {
                announcer.textContent = ''; 
                setTimeout(() => { announcer.textContent = message; }, 100); 
            };

            window.toggleHighContrast = function() {
                document.body.classList.toggle('high-contrast');
                const isActive = document.body.classList.contains('high-contrast');
                localStorage.setItem('highContrast', isActive);
                announce(isActive ? "Modo de alto contraste ativado" : "Modo de alto contraste desativado");
            };
            if(localStorage.getItem('highContrast') === 'true') {
                document.body.classList.add('high-contrast');
            }

            // SISTEMA DE ALERTA GLOBAL
            window.showAlert = function(title, message, type = 'info') {
                const modal = document.getElementById('globalAlertModal');
                const titleEl = document.getElementById('globalAlertTitle');
                const msgEl = document.getElementById('globalAlertMessage');
                const iconEl = document.getElementById('globalAlertIcon');
                const btn = document.getElementById('globalAlertBtn');

                if(!modal) return alert(message);

                titleEl.textContent = title;
                msgEl.textContent = message;
                
                if(type === 'error') {
                    iconEl.innerHTML = '<i class="ri-error-warning-fill text-rose-500"></i>';
                    btn.className = "close-modal-btn w-full py-3 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-lg transition-all";
                } else {
                    iconEl.innerHTML = '<i class="ri-information-fill text-indigo-500"></i>';
                    btn.className = "close-modal-btn w-full py-3 rounded-xl font-bold text-white bg-indigo-500 hover:bg-indigo-600 shadow-lg transition-all";
                }

                modal.classList.remove('hidden');
            };

            window.closeAlert = function() {
                document.getElementById('globalAlertModal').classList.add('hidden');
            };

            // LOGICA DO SOS
            const sosBtns = document.querySelectorAll('#sosBtnTrigger, .sos-trigger'); 
            const modal = document.getElementById('sosModal');
            const overlay = document.getElementById('modalOverlay');
            const closeBtn = document.getElementById('modalClose');

            function toggleModal() { modal.classList.toggle('hidden'); }
            if(modal) {
                sosBtns.forEach(btn => btn.addEventListener('click', toggleModal));
                if(overlay) overlay.addEventListener('click', toggleModal);
                if(closeBtn) closeBtn.addEventListener('click', toggleModal);
            }

            @auth
                if (window.Echo) {
                    window.Echo.private('App.Models.User.{{ Auth::id() }}')
                        .notification((notification) => {
                            window.dispatchEvent(new CustomEvent('new-notification'));
                        });
                }
            @endauth

            // Intercetor Global: Se um pedido demorar mais de 400ms, mostra o ecrã de respiração
            let loaderTimeout;
            axios.interceptors.request.use(config => {
                if(config.method !== 'get') { // Apenas ao guardar ou reagir
                    loaderTimeout = setTimeout(() => { document.getElementById('calm-loader').classList.remove('opacity-0', 'pointer-events-none'); }, 400);
                }
                return config;
            });
            axios.interceptors.response.use(res => {
                clearTimeout(loaderTimeout); document.getElementById('calm-loader').classList.add('opacity-0', 'pointer-events-none'); return res;
            }, err => {
                clearTimeout(loaderTimeout); document.getElementById('calm-loader').classList.add('opacity-0', 'pointer-events-none'); return Promise.reject(err);
            });
        });
    </script>

    <script>
        /**
         * Acionado pelo botão "Saída Rápida" (geralmente fixo no ecrã ou ativado via tecla 'Esc' dupla).
         * Camufla o histórico, destrói a sessão e muda a interface imediatamente.
         */
        function triggerSafeHouse() {
            // 1. Camuflagem imediata de UI (Evita o flash visual durante o redirecionamento)
            document.body.innerHTML = '';
            document.title = "Google";
            
            // 2. Mudança de Favicon
            let link = document.querySelector("link[rel*='icon']") || document.createElement('link');
            link.type = 'image/x-icon';
            link.rel = 'shortcut icon';
            link.href = 'https://www.google.com/favicon.ico';
            document.getElementsByTagName('head')[0].appendChild(link);

            // 3. Destruição de dados locais do browser
            window.localStorage.clear();
            window.sessionStorage.clear();

            // 4. Manipulação de Histórico
            // Remove a página atual do histórico do browser
            window.history.replaceState(null, '', 'https://www.google.com');

            // 5. Invalidação de Sessão Server-side (Logout Invisível)
            // Submetemos o form de logout atual (se existir) para destruir o cookie de sessão Laravel,
            // mas redirecionamos a janela imediatamente para segurança física.
            const logoutForm = document.createElement('form');
            logoutForm.method = 'POST';
            logoutForm.action = '{{ route("logout") ?? "#" }}';
            logoutForm.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            logoutForm.appendChild(csrfToken);
            document.body.appendChild(logoutForm);
            
            try { logoutForm.submit(); } catch(e) {}

            window.location.replace("https://www.google.com");
        }

        // Ativador Global: Duplo clique na tecla ESCapatória
        let escCount = 0;
        let escTimeout = null;
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                escCount++;
                if (escCount >= 2) {
                    triggerSafeHouse();
                } else {
                    escTimeout = setTimeout(() => { escCount = 0; }, 500); // Meio segundo para o duplo clique
                }
            }
        });
    </script>
    
    {{ $scripts ?? '' }}
</body>
</html>