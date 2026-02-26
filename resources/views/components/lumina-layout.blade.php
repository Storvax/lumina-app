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
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .mesh-gradient { background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.1) 0%, rgba(255, 255, 255, 0) 50%), radial-gradient(circle at 100% 0%, rgba(20, 184, 166, 0.1) 0%, rgba(255, 255, 255, 0) 50%); }
        .animate-fade-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        #night-mode-filter { background-color: #ff9900; mix-blend-mode: multiply; opacity: 0; transition: opacity 2s ease-in-out; pointer-events: none; z-index: 9999; }
        
        body.high-contrast { background-color: #ffffff !important; color: #000000 !important; }
        body.high-contrast .glass-card, body.high-contrast .glass, body.high-contrast nav .glass { background: #ffffff !important; backdrop-filter: none !important; border: 2px solid #000000 !important; box-shadow: none !important; }
        body.high-contrast .text-slate-400, body.high-contrast .text-slate-500, body.high-contrast .text-slate-600 { color: #000000 !important; }
        body.high-contrast button, body.high-contrast a { text-decoration: underline; font-weight: 700 !important; }
        :focus-visible { outline: 3px solid #000000 !important; outline-offset: 2px; }

        .wave-effect { position: relative; overflow: hidden; }
        .wave-effect::after { content: ''; position: absolute; top: 50%; left: 50%; width: 100%; height: 100%; background: currentColor; border-radius: 50%; transform: translate(-50%, -50%) scale(0); opacity: 0.2; pointer-events: none; }
        .wave-effect.active::after { animation: expandWave 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; }
        @keyframes expandWave { 0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.3; } 100% { transform: translate(-50%, -50%) scale(4); opacity: 0; } }

        /* Modo Madrugada (00h–05h): ajustes visuais subtis para acompanhar o utilizador */
        body.madrugada-mode { font-size: 105%; }
        body.madrugada-mode * { scroll-behavior: smooth; }
        /* O filtro de cor já é reforçado via JavaScript; esta regra serve de fallback de CSS */
        body.madrugada-mode #night-mode-filter { opacity: 0.12 !important; }

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

    <div id="sosModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div id="modalOverlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity cursor-pointer"></div>
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-rose-100 animate-fade-up z-10">
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

    <div id="globalAlertModal" class="fixed inset-0 z-[150] hidden flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeAlert()"></div>
        <div id="globalAlertPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center border-2 border-transparent high-contrast:border-black transform transition-all scale-100 z-10">
            <div id="globalAlertIcon" class="mb-4 text-4xl"></div>
            <h3 id="globalAlertTitle" class="text-xl font-bold text-slate-900 mb-2"></h3>
            <p id="globalAlertMessage" class="text-slate-600 mb-6 text-sm"></p>
            <button id="globalAlertBtn" onclick="closeAlert()" class="close-modal-btn w-full py-3 rounded-xl font-bold text-white shadow-lg transition-all active:scale-95">Entendido</button>
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
                    @auth
                        <a href="{{ route('diary.index') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('diary.*') ? 'text-indigo-600 font-bold' : '' }}">Diário</a>
                        <a href="{{ route('calm.index') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('calm.*') ? 'text-indigo-600 font-bold' : '' }}">Zona Calma</a>
                    @endauth
                    <a href="{{ route('library.index') ?? url('/#biblioteca') }}" class="text-slate-600 hover:text-indigo-600 transition-colors {{ request()->routeIs('library.*') ? 'text-indigo-600 font-bold' : '' }}">Biblioteca</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <div class="relative" x-data="{ open: false, count: {{ Auth::user()->unreadNotifications->count() }} }" x-on:new-notification.window="count++">
                            <button type="button" @click.prevent="open = !open; if(open) { axios.post('{{ route('notifications.read') }}'); count = 0; }" 
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
                                    <button type="button" @click.prevent="open = false" class="text-slate-300 hover:text-slate-500"><i class="ri-close-line"></i></button>
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

                    @auth
                        @if(Auth::user()->safety_plan)
                            {{-- Acesso rápido ao plano de segurança pessoal, visível apenas quando o plano existe --}}
                            <a href="{{ route('calm.crisis') }}"
                               title="Ver o meu plano de segurança"
                               class="hidden md:flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-100 px-3 py-2 rounded-full text-xs font-bold transition-all">
                                <i class="ri-shield-heart-line text-base"></i>
                                <span>O meu plano</span>
                            </a>
                        @endif
                    @endauth

                    <button type="button" id="sosBtnTrigger" class="bg-white border border-rose-100 text-rose-500 hover:bg-rose-50 hover:border-rose-200 px-3 md:px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
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
    
    <main class="flex-1 w-full pt-28 pb-24 md:pb-12">
        {{ $slot }}
    </main>

    @auth
        @php
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

                {{-- Botão central — Zona Calma (prioritário se o utilizador tem ansiedade/sobrecarga) ou Fogueira --}}
                @if($needsCalm)
                    <a href="{{ route('calm.index') }}" class="flex flex-col items-center justify-center w-14 h-14 -mt-8 bg-teal-500 text-white rounded-full shadow-lg shadow-teal-500/30 ring-4 ring-white animate-[pulse_4s_ease-in-out_infinite]">
                        <i class="ri-lungs-fill text-2xl"></i>
                    </a>
                @else
                    <a href="{{ route('rooms.index') }}" class="flex flex-col items-center justify-center w-14 h-14 -mt-8 bg-orange-500 text-white rounded-full shadow-lg shadow-orange-500/30 ring-4 ring-white">
                        <i class="ri-fire-fill text-2xl"></i>
                    </a>
                @endif

                <a href="{{ route('diary.index') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-teal-600 {{ request()->routeIs('diary.*') ? 'text-teal-600' : '' }}">
                    <i class="ri-book-read-{{ request()->routeIs('diary.*') ? 'fill' : 'line' }} text-2xl"></i>
                    <span class="text-[9px] font-bold">Diário</span>
                </a>

                <a href="{{ route('profile.show') }}" class="flex flex-col items-center gap-1 w-14 text-slate-400 hover:text-indigo-600 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : '' }}">
                    <i class="ri-user-smile-{{ request()->routeIs('profile.*') ? 'fill' : 'line' }} text-2xl"></i>
                    <span class="text-[9px] font-bold">Perfil</span>
                </a>
            </div>
        </div>
    @endauth

    @include('partials.footer', ['bottomPadding' => 'pb-28 md:pb-10'])

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hour = new Date().getHours();
            const isMadrugada = hour >= 0 && hour < 5;
            const isNight     = hour >= 21 || hour < 6;

            if (isMadrugada) {
                // Madrugada (00h–05h): filtro mais intenso e banner de apoio
                document.getElementById('night-mode-filter').style.opacity = '0.12';
                document.body.classList.add('madrugada-mode');
                _showMadrugadaBanner();
            } else if (isNight) {
                // Noite normal (21h–00h e 05h–06h): filtro suave
                document.getElementById('night-mode-filter').style.opacity = '0.07';
            }

            function _showMadrugadaBanner() {
                // Verifica se o utilizador já fechou o banner nesta sessão
                if (sessionStorage.getItem('madrugada-banner-dismissed')) return;

                const banner = document.createElement('div');
                banner.id = 'madrugada-banner';
                banner.setAttribute('role', 'complementary');
                banner.setAttribute('aria-label', 'Apoio nocturno');
                banner.className = [
                    'fixed bottom-20 md:bottom-6 left-4 right-4',
                    'md:left-auto md:right-6 md:max-w-sm',
                    'z-40 bg-indigo-950/95 backdrop-blur-xl',
                    'text-white rounded-2xl p-5',
                    'border border-indigo-700/50 shadow-2xl',
                    'animate-fade-up'
                ].join(' ');

                banner.innerHTML = [
                    '<div class="flex items-start gap-3">',
                        '<i class="ri-moon-foggy-line text-2xl text-indigo-300 shrink-0 mt-0.5" aria-hidden="true"></i>',
                        '<div class="flex-1 min-w-0">',
                            '<p class="font-bold text-sm text-indigo-100 leading-snug">É tarde. O teu cérebro está mais vulnerável a esta hora.</p>',
                            '<p class="text-xs text-indigo-300 mt-1 leading-relaxed">Estamos aqui contigo. Respira fundo.</p>',
                            '<div class="flex flex-wrap gap-2 mt-3">',
                                '<a href="/zona-calma/crise"',
                                '   class="text-xs font-bold bg-indigo-700 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">',
                                '    Zona de Crise',
                                '</a>',
                                '<a href="/zona-calma/grounding"',
                                '   class="text-xs font-bold bg-indigo-800/60 hover:bg-indigo-700 text-indigo-200 px-3 py-1.5 rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">',
                                '    Grounding',
                                '</a>',
                                '<button id="madrugada-dismiss"',
                                '        class="text-xs text-indigo-400 hover:text-indigo-200 px-2 py-1.5 transition-colors ml-auto focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white rounded"',
                                '        aria-label="Fechar aviso nocturno">',
                                '    Fechar',
                                '</button>',
                            '</div>',
                        '</div>',
                    '</div>',
                ].join('');

                document.body.appendChild(banner);

                document.getElementById('madrugada-dismiss').addEventListener('click', function () {
                    sessionStorage.setItem('madrugada-banner-dismissed', '1');
                    banner.remove();
                });
            }

            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('class', 'sr-only');
            document.body.appendChild(announcer);

            window.announce = function(message) { announcer.textContent = ''; setTimeout(() => { announcer.textContent = message; }, 100); };

            window.showAlert = function(title, message, type = 'info') {
                const modal = document.getElementById('globalAlertModal');
                document.getElementById('globalAlertTitle').textContent = title;
                document.getElementById('globalAlertMessage').textContent = message;
                const iconEl = document.getElementById('globalAlertIcon');
                const btn = document.getElementById('globalAlertBtn');
                
                if(type === 'error') {
                    iconEl.innerHTML = '<i class="ri-error-warning-fill text-rose-500"></i>';
                    btn.className = "close-modal-btn w-full py-3 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-lg transition-all";
                } else {
                    iconEl.innerHTML = '<i class="ri-information-fill text-indigo-500"></i>';
                    btn.className = "close-modal-btn w-full py-3 rounded-xl font-bold text-white bg-indigo-500 hover:bg-indigo-600 shadow-lg transition-all";
                }
                modal.classList.remove('hidden');
            };

            window.closeAlert = function() { document.getElementById('globalAlertModal').classList.add('hidden'); };

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

            let loaderTimeout;
            axios.interceptors.request.use(config => {
                if(config.method !== 'get') { 
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

        function triggerSafeHouse() {
            document.body.innerHTML = '';
            document.title = "Google";
            let link = document.querySelector("link[rel*='icon']") || document.createElement('link');
            link.type = 'image/x-icon'; link.rel = 'shortcut icon'; link.href = 'https://www.google.com/favicon.ico';
            document.getElementsByTagName('head')[0].appendChild(link);
            window.localStorage.clear(); window.sessionStorage.clear();
            window.history.replaceState(null, '', 'https://www.google.com');
            const logoutForm = document.createElement('form'); logoutForm.method = 'POST'; logoutForm.action = '{{ route("logout") ?? "#" }}'; logoutForm.style.display = 'none';
            const csrfToken = document.createElement('input'); csrfToken.type = 'hidden'; csrfToken.name = '_token'; csrfToken.value = '{{ csrf_token() }}';
            logoutForm.appendChild(csrfToken); document.body.appendChild(logoutForm);
            try { logoutForm.submit(); } catch(e) {}
            window.location.replace("https://www.google.com");
        }

        let escCount = 0, escTimeout = null;
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                escCount++;
                if (escCount >= 2) { triggerSafeHouse(); } 
                else { escTimeout = setTimeout(() => { escCount = 0; }, 500); }
            }
        });
    </script>
    
    @if(isset($scripts))
        {{ $scripts }}
    @endif
</body>
</html>