<nav class="fixed top-0 w-full z-50 transition-all duration-300 backdrop-blur-sm" id="main-nav">
    <div class="glass max-w-6xl mt-2 sm:mt-4 md:rounded-full rounded-2xl px-2 sm:px-4 md:px-6 py-2 sm:py-3 flex justify-between items-center shadow-lg shadow-black/5 mx-2 sm:mx-4 md:mx-auto border border-white/20 dark:border-slate-700/50 dark:bg-slate-900/80">

        <a href="{{ url('/') }}" class="flex items-center gap-1.5 sm:gap-2 group shrink-0">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-tr from-primary-500 to-indigo-400 flex items-center justify-center text-white font-bold text-base sm:text-lg group-hover:rotate-12 transition-transform">L</div>
            <span class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white tracking-tight">Lumina<span class="text-primary-500">.</span></span>
        </a>

        <div class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="#inicio" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Início</a>
            <a href="#calma" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Zona Calma</a>
            <a href="#comunidade" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Comunidade</a>
            <a href="#forum" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Fórum</a>
            <a href="#biblioteca" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Biblioteca</a>
        </div>

        <div class="flex items-center gap-1 sm:gap-2 md:gap-3">
            <button id="theme-toggle" type="button"
                class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full text-sm p-1.5 sm:p-2.5 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Alternar modo escuro">
                <i id="theme-toggle-dark-icon" class="ri-moon-line hidden text-base sm:text-lg"></i>
                <i id="theme-toggle-light-icon" class="ri-sun-line hidden text-base sm:text-lg text-amber-400"></i>
            </button>

            {{-- Utilizadores autenticados são redirecionados para o dashboard pelo HomeController --}}
            @guest
                <a href="{{ route('login') }}" class="hidden md:flex text-sm font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-slate-800 px-4 py-2 rounded-full transition-colors">Iniciar sessão</a>
                <a href="{{ route('register') }}" class="hidden md:flex text-sm font-bold bg-primary-500 hover:bg-primary-600 text-white px-5 py-2 rounded-full transition-colors shadow-sm">Criar conta</a>
            @endguest

            <button class="bg-white dark:bg-slate-800 border border-rose-100 dark:border-rose-900/30 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-200 px-2 sm:px-3 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-bold flex items-center gap-1.5 transition-all shadow-sm shrink-0" onclick="document.getElementById('sosModal').classList.remove('hidden')">
                <span class="relative flex h-2 w-2 shrink-0">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                </span>
                SOS
            </button>

            <button id="mobileMenuBtn"
                    class="md:hidden text-slate-600 dark:text-slate-300 p-1.5 sm:p-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg"
                    aria-label="Abrir menu" aria-expanded="false" aria-controls="mobileMenu">
                <i class="ri-menu-line text-xl sm:text-2xl"></i>
            </button>
        </div>
    </div>

    {{-- Menu Mobile: posicionado dinamicamente via JS --}}
    <div id="mobileMenu"
         class="hidden absolute left-2 right-2 sm:left-3 sm:right-3 bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 p-4 sm:p-6 z-40 md:hidden"
         style="top: calc(100% + 4px);"
         role="dialog" aria-label="Menu de navegação">
        <div class="flex flex-col gap-0.5">
            <a href="#inicio"     class="mobile-link py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl text-sm sm:text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">Início</a>
            <a href="#calma"      class="mobile-link py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl text-sm sm:text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">Zona Calma</a>
            <a href="#comunidade" class="mobile-link py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl text-sm sm:text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">Comunidade</a>
            <a href="#forum"      class="mobile-link py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl text-sm sm:text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">Fórum</a>
            <a href="#biblioteca" class="mobile-link py-2.5 sm:py-3 px-3 sm:px-4 rounded-xl text-sm sm:text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">Biblioteca</a>
        </div>
        <hr class="border-slate-100 dark:border-slate-800 my-3 sm:my-4">
        @guest
            <div class="flex flex-col gap-2 sm:gap-3">
                <a href="{{ route('register') }}" class="text-center w-full py-2.5 sm:py-3 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-bold transition-colors text-sm sm:text-base">
                    Criar conta gratuita
                </a>
                <a href="{{ route('login') }}" class="text-center w-full py-2.5 sm:py-3 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-100 transition-colors text-sm sm:text-base">
                    Iniciar sessão
                </a>
            </div>
        @endguest
    </div>
</nav>