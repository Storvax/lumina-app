<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
        
        /* --- MODO ALTO CONTRASTE --- */
        body.high-contrast {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        body.high-contrast .glass-card, 
        body.high-contrast .glass,
        body.high-contrast nav .glass {
            background: #ffffff !important;
            backdrop-filter: none !important;
            border: 2px solid #000000 !important;
            box-shadow: none !important;
        }
        body.high-contrast .text-slate-400, 
        body.high-contrast .text-slate-500, 
        body.high-contrast .text-slate-600 {
            color: #000000 !important;
        }
        body.high-contrast button, 
        body.high-contrast a {
            text-decoration: underline;
            font-weight: 700 !important;
        }
        :focus-visible {
            outline: 3px solid #000000 !important;
            outline-offset: 2px;
        }

        {{ $css ?? '' }}
    </style>
</head>
<body class="antialiased text-slate-600 bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white relative flex flex-col min-h-screen">

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

    <div class="fixed bottom-6 right-6 z-[90]">
        <button onclick="toggleHighContrast()" 
                class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center shadow-lg hover:scale-110 transition-transform focus:ring-4 ring-offset-2 ring-slate-900 high-contrast:border-2 high-contrast:border-white"
                aria-label="Alternar modo de alto contraste"
                title="Alto Contraste">
            <i class="ri-contrast-drop-2-line text-xl"></i>
        </button>
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
                        <a href="{{ route('calm.crisis') }}" class="hidden lg:flex items-center gap-2 px-4 py-1.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl hover:bg-rose-100 transition-all font-bold text-sm">
                            <i class="ri-alarm-warning-line text-lg"></i> Modo Crise
                        </a>

                        <div class="relative" x-data="{ open: false, count: {{ Auth::user()->unreadNotifications->count() }} }" x-on:new-notification.window="count++">
                            <button @click="open = !open; if(open) { axios.post('{{ route('notifications.read') }}'); count = 0; }" 
                                    class="relative w-9 h-9 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                <i class="ri-notification-3-line"></i>
                                <span x-show="count > 0" x-text="count" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white animate-pulse"></span>
                            </button>

                            <div x-show="open" @click.outside="open = false" 
                                 x-transition:enter="transition ease-out duration-200 opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute right-0 top-full mt-3 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden py-2" style="display: none;">
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
                        <a href="{{ route('login') }}" class="hidden md:flex text-sm font-semibold text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-full transition-colors">Entrar</a>
                    @endauth

                    @if(isset($actionButton)) {{ $actionButton }} @endif

                    <button id="sosBtnTrigger" class="bg-white border border-rose-100 text-rose-500 hover:bg-rose-50 hover:border-rose-200 px-3 md:px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                        </span>
                        SOS
                    </button>
                    
                    <button id="mobileMenuBtn" class="md:hidden text-slate-600 p-2 focus:outline-none"><i class="ri-menu-line text-2xl"></i></button>
                </div>
            </div>
            
            <div id="mobileMenu" class="hidden absolute top-20 left-4 right-4 bg-white rounded-3xl shadow-xl border border-slate-100 p-6 flex flex-col gap-4 animate-fade-up md:hidden">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-lg font-medium text-slate-600">Dashboard</a>
                @else
                    <a href="{{ url('/') }}" class="text-lg font-medium text-slate-600">Início</a>
                @endauth
                <a href="{{ route('forum.index') }}" class="text-lg font-medium text-slate-600">Mural da Esperança</a>
                <a href="{{ route('rooms.index') }}" class="text-lg font-medium text-slate-600">A Fogueira (Chat)</a>
                <a href="{{ route('calm.index') }}" class="text-lg font-medium text-indigo-600 flex items-center gap-2"><i class="ri-leaf-line"></i> Zona Calma</a>
                
                @auth 
                    <a href="{{ route('calm.crisis') }}" class="text-lg font-medium text-rose-600 flex items-center gap-2 bg-rose-50 p-3 rounded-xl border border-rose-100 mt-2">
                        <i class="ri-alarm-warning-line"></i> Modo Crise
                    </a>
                    <hr class="border-slate-100 mt-2">
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2 text-lg font-medium text-slate-600">
                        <i class="ri-user-line"></i> Meu Perfil
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-lg font-medium text-slate-600">
                        <i class="ri-settings-3-line"></i> Definições
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2"> 
                        @csrf
                        <button type="submit" class="text-slate-400 font-medium">Sair</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-center w-full py-3 rounded-xl bg-indigo-50 text-indigo-600 font-bold mt-4">Entrar / Registar</a>
                @endauth
            </div>
        </nav>
        @endif

    <div class="fixed top-0 left-0 w-full h-full mesh-gradient opacity-60 -z-10 pointer-events-none"></div>
    
    <main class="flex-1 w-full pt-32 pb-12">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t border-slate-100 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1 space-y-4">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-lg">L</div>
                        <span class="text-xl font-bold text-slate-900">Lumina.</span>
                    </a>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Democratizar o acesso ao bem-estar mental em Portugal, criando pontes entre pessoas e profissionais.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"><i class="ri-instagram-line"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"><i class="ri-twitter-x-line"></i></a>
                    </div>
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
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Regras da Comunidade</a></li>
                    </ul>
                </div>

                <div>
                    <div class="bg-amber-50 border border-amber-100 p-5 rounded-2xl">
                        <p class="text-xs font-bold text-amber-700 uppercase mb-2 flex items-center gap-1">
                            <i class="ri-alert-line"></i> Importante
                        </p>
                        <p class="text-xs text-amber-800/80 leading-relaxed">
                            A Lumina não presta atos médicos. Em caso de emergência ou risco de vida, liga imediatamente para o <span class="font-bold">112</span> ou <span class="font-bold">SNS24 (808 24 24 24)</span>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-400">© {{ date('Y') }} Lumina Portugal. Todos os direitos reservados.</p>
                <p class="text-xs text-slate-400 flex items-center gap-1">Feito com <i class="ri-heart-fill text-rose-400"></i> e empatia.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- GESTOR DE ACESSIBILIDADE LUMINA ---
            
            // 1. ANUNCIADOR DE VOZ (ARIA LIVE)
            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('class', 'sr-only');
            document.body.appendChild(announcer);

            window.announce = function(message) {
                announcer.textContent = ''; 
                setTimeout(() => { announcer.textContent = message; }, 100); 
            };

            // 2. FOCUS TRAP (Para Modais)
            window.trapFocus = function(modalElement) {
                const focusableElements = modalElement.querySelectorAll('a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select');
                if (focusableElements.length === 0) return;

                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                modalElement.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        if (e.shiftKey) { // Shift + Tab
                            if (document.activeElement === firstElement) {
                                e.preventDefault();
                                lastElement.focus();
                            }
                        } else { // Tab
                            if (document.activeElement === lastElement) {
                                e.preventDefault();
                                firstElement.focus();
                            }
                        }
                    }
                });
                setTimeout(() => firstElement.focus(), 100);
            };

            // 3. MODO ALTO CONTRASTE
            window.toggleHighContrast = function() {
                document.body.classList.toggle('high-contrast');
                const isActive = document.body.classList.contains('high-contrast');
                localStorage.setItem('highContrast', isActive);
                announce(isActive ? "Modo de alto contraste ativado" : "Modo de alto contraste desativado");
            };
            if(localStorage.getItem('highContrast') === 'true') {
                document.body.classList.add('high-contrast');
            }

            // 4. SISTEMA DE ALERTA GLOBAL (Substitui o alert nativo)
            window.showAlert = function(title, message, type = 'info') {
                const modal = document.getElementById('globalAlertModal');
                const panel = document.getElementById('globalAlertPanel');
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
                trapFocus(panel);
                announce(`Alerta: ${title}. ${message}`);
            };

            window.closeAlert = function() {
                const modal = document.getElementById('globalAlertModal');
                modal.classList.add('hidden');
                if(window.lastFocusedElement) window.lastFocusedElement.focus();
            };

            // LOGICA DOS MENUS E SOS
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

            const mobileBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            if(mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });
            }

            @auth
                if (window.Echo) {
                    window.Echo.private('App.Models.User.{{ Auth::id() }}')
                        .notification((notification) => {
                            window.dispatchEvent(new CustomEvent('new-notification'));
                        });
                }
            @endauth
        });
    </script>
    
    {{ $scripts ?? '' }}
</body>
</html>