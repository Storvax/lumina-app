<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A Fogueira | Comunidade Lumina</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased text-slate-600 bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white relative">

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
                            <div class="text-left">
                                <p class="font-bold text-slate-800">Emergência Nacional</p>
                                <p class="text-xs text-slate-500">Risco de vida iminente</p>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-rose-500 shadow-sm">
                            <i class="ri-phone-fill"></i>
                        </div>
                    </a>
                    <a href="tel:808242424" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-50 hover:border-blue-200 transition-colors group">
                        <div class="flex items-center gap-4">
                            <span class="text-xl font-bold text-slate-800 group-hover:text-blue-600">SNS 24</span>
                            <div class="text-left">
                                <p class="font-bold text-slate-800">Apoio Psicológico</p>
                                <p class="text-xs text-slate-500">Disponível 24h por dia</p>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-500 shadow-sm">
                            <i class="ri-phone-fill"></i>
                        </div>
                    </a>
                </div>
                <div class="bg-slate-50 p-4 text-center">
                    <button id="modalClose" class="text-slate-500 font-semibold hover:text-slate-800 text-sm">Cancelar / Voltar ao site</button>
                </div>
            </div>
        </div>
    </div>

    <a href="https://www.google.pt" class="fixed bottom-6 right-6 z-[60] bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full shadow-xl flex items-center gap-2 transition-transform hover:scale-105 border-4 border-white ring-2 ring-red-100" title="Sair rapidamente para o Google">
        <i class="ri-eye-off-line text-xl"></i> 
        <span class="hidden md:inline">Saída Rápida</span>
    </a>

    <nav class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="glass max-w-6xl mx-auto mt-4 md:rounded-full rounded-2xl px-6 py-3 flex justify-between items-center shadow-lg shadow-black/5 mx-4 md:mx-auto">
            <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-primary-500 to-indigo-400 flex items-center justify-center text-white font-bold text-lg group-hover:rotate-12 transition-transform">L</div>
                <span class="text-xl font-bold text-slate-800 tracking-tight">Lumina<span class="text-primary-500">.</span></span>
            </a>

            <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ url('/') }}" class="text-slate-600 hover:text-primary-600 transition-colors">Início</a>
                <a href="{{ url('/#calma') }}" class="text-slate-600 hover:text-primary-600 transition-colors">Zona Calma</a>
                <a href="{{ route('rooms.index') }}" class="text-primary-600 font-bold transition-colors">Comunidade</a>
                <a href="{{ url('/#forum') }}" class="text-slate-600 hover:text-primary-600 transition-colors">Fórum</a>
                <a href="{{ url('/#biblioteca') }}" class="text-slate-600 hover:text-primary-600 transition-colors">Biblioteca</a>
            </div>

            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hidden md:flex text-sm font-semibold text-primary-600 hover:bg-primary-50 px-4 py-2 rounded-full transition-colors">
                            Minha Conta
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden md:flex text-sm font-semibold text-primary-600 hover:bg-primary-50 px-4 py-2 rounded-full transition-colors">
                            Login Anónimo
                        </a>
                    @endauth
                @endif

                <button class="bg-white border border-rose-100 text-rose-500 hover:bg-rose-50 hover:border-rose-200 px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                    </span>
                    SOS
                </button>
                
                <button id="mobileMenuBtn" class="md:hidden text-slate-600 p-2 focus:outline-none">
                    <i class="ri-menu-line text-2xl"></i>
                </button>
            </div>
        </div>

        <div id="mobileMenu" class="hidden absolute top-20 left-4 right-4 bg-white rounded-3xl shadow-xl border border-slate-100 p-6 flex flex-col gap-4 animate-fade-up md:hidden">
            <a href="{{ url('/') }}" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Início</a>
            <a href="{{ url('/#calma') }}" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Zona Calma</a>
            <a href="{{ route('rooms.index') }}" class="mobile-link text-lg font-medium text-primary-600 font-bold">Comunidade</a>
            <a href="{{ url('/#forum') }}" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Fórum</a>
            <a href="{{ url('/#biblioteca') }}" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Biblioteca</a>
            <hr class="border-slate-100">
            @auth
                <a href="{{ url('/dashboard') }}" class="text-center w-full py-3 rounded-xl bg-primary-50 text-primary-600 font-bold">Minha Conta</a>
            @else
                <a href="{{ route('login') }}" class="text-center w-full py-3 rounded-xl bg-primary-50 text-primary-600 font-bold">Login Anónimo</a>
            @endauth
        </div>
    </nav>

    <div class="pt-40 pb-24 min-h-screen">
        <div class="max-w-7xl mx-auto px-6 mb-16 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-primary-50 text-primary-600 text-xs font-bold uppercase tracking-wider mb-4">Grupos de Apoio</span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-6">Escolhe a tua <span class="text-primary-500">Fogueira</span></h1>
            <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed">
                Estas salas são espaços seguros, anónimos e moderados. Entra, ouve o que os outros têm a dizer e partilha quando te sentires pronto.
            </p>
        </div>

        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @foreach($rooms as $room)
                <a href="{{ route('chat.show', $room) }}" class="group relative bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden cursor-pointer h-full flex flex-col block">
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-{{ $room->color }}-50 rounded-bl-[100px] -mr-8 -mt-8 z-0 transition-transform group-hover:scale-110"></div>
                    
                    <div class="relative z-10 flex-grow">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-{{ $room->color }}-100 text-{{ $room->color }}-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                            <i class="{{ $room->icon }}"></i>
                        </div>
                        
                        <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $room->name }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-6">{{ $room->description }}</p>
                    </div>

                    <div class="relative z-10 mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] text-slate-500">A</div>
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[10px] text-slate-500">B</div>
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-{{ $room->color }}-100 flex items-center justify-center text-[10px] font-bold text-{{ $room->color }}-600">+8</div>
                        </div>
                        
                        <span class="text-{{ $room->color }}-600 font-bold text-sm flex items-center gap-1 group-hover:underline">
                            Entrar <i class="ri-arrow-right-line"></i>
                        </span>
                    </div>
                </a>
                @endforeach

            </div>
        </div>
    </div>

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
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"><i class="ri-instagram-line"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"><i class="ri-twitter-x-line"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"><i class="ri-linkedin-fill"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-slate-900 mb-6">Plataforma</h4>
                    <ul class="space-y-3 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-primary-600 transition-colors">A Fogueira (Salas)</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Ouvintes</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Para Psicólogos</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Planos para Empresas</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 mb-6">Legal</h4>
                    <ul class="space-y-3 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Termos de Uso</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Regras da Comunidade</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Livro de Reclamações</a></li>
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
                <p class="text-xs text-slate-400">© 2026 Lumina Portugal. Todos os direitos reservados.</p>
                <p class="text-xs text-slate-400 flex items-center gap-1">Feito com <i class="ri-heart-fill text-rose-400"></i> e empatia.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Lógica do Modal SOS
            const allButtons = document.querySelectorAll('button');
            const sosBtn = Array.from(allButtons).find(btn => btn.textContent.includes('SOS'));
            const modal = document.getElementById('sosModal');
            const overlay = document.getElementById('modalOverlay');
            const closeBtn = document.getElementById('modalClose');

            function toggleModal() {
                modal.classList.toggle('hidden');
            }

            if(sosBtn) sosBtn.addEventListener('click', toggleModal);
            if(overlay) overlay.addEventListener('click', toggleModal);
            if(closeBtn) closeBtn.addEventListener('click', toggleModal);

            // Lógica do Menu Mobile
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileLinks = document.querySelectorAll('.mobile-link');

            if(mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                    });
                });
            }
        });
    </script>

</body>
</html>