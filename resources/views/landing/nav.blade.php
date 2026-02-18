<nav class="fixed top-0 w-full z-50 transition-all duration-300 backdrop-blur-sm" id="main-nav">
    <div class="glass max-w-6xl mx-auto mt-4 md:rounded-full rounded-2xl px-6 py-3 flex justify-between items-center shadow-lg shadow-black/5 mx-4 md:mx-auto border border-white/20 dark:border-slate-700/50 dark:bg-slate-900/80">
        
        <a href="{{ url('/') }}" class="flex items-center gap-2 group">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-primary-500 to-indigo-400 flex items-center justify-center text-white font-bold text-lg group-hover:rotate-12 transition-transform">L</div>
            <span class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Lumina<span class="text-primary-500">.</span></span>
        </a>

        <div class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="#inicio" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Início</a>
            <a href="#calma" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Zona Calma</a>
            <a href="#comunidade" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Comunidade</a>
            <a href="#forum" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Fórum</a>
            <a href="#biblioteca" class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Biblioteca</a>
        </div>

        <div class="flex items-center gap-3">
            <button id="theme-toggle" type="button" 
                class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full text-sm p-2.5 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Alternar modo escuro">
                <i id="theme-toggle-dark-icon" class="ri-moon-line hidden text-lg"></i>
                <i id="theme-toggle-light-icon" class="ri-sun-line hidden text-lg text-amber-400"></i>
            </button>

            @auth
                <a href="{{ url('/dashboard') }}" class="hidden md:flex text-sm font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-slate-800 px-4 py-2 rounded-full transition-colors">Minha Conta</a>
            @else
                <a href="{{ route('login') }}" class="hidden md:flex text-sm font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-slate-800 px-4 py-2 rounded-full transition-colors">Login Anónimo</a>
            @endauth

            <button class="bg-white dark:bg-slate-800 border border-rose-100 dark:border-rose-900/30 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-200 px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 transition-all shadow-sm" onclick="document.getElementById('sosModal').classList.remove('hidden')">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                </span>
                SOS
            </button>
            
            <button id="mobileMenuBtn" class="md:hidden text-slate-600 dark:text-slate-300 p-2 focus:outline-none"><i class="ri-menu-line text-2xl"></i></button>
        </div>
    </div>

    <div id="mobileMenu" class="hidden absolute top-24 left-4 right-4 bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-100 dark:border-slate-800 p-6 flex flex-col gap-4 animate-fade-up md:hidden">
        <a href="#inicio" class="mobile-link text-lg font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600">Início</a>
        <a href="#calma" class="mobile-link text-lg font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600">Zona Calma</a>
        <a href="#comunidade" class="mobile-link text-lg font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600">Comunidade</a>
        <a href="#forum" class="mobile-link text-lg font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600">Fórum</a>
        <a href="#biblioteca" class="mobile-link text-lg font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600">Biblioteca</a>
        <hr class="border-slate-100 dark:border-slate-800">
        @auth
            <a href="{{ url('/dashboard') }}" class="text-center w-full py-3 rounded-xl bg-primary-50 dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-bold">Minha Conta</a>
        @else
            <a href="{{ route('login') }}" class="text-center w-full py-3 rounded-xl bg-primary-50 dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-bold">Login Anónimo</a>
        @endauth
    </div>
</nav>