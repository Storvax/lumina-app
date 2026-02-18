<section id="comunidade" class="py-24 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm relative transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16 scroll-reveal">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-4">O teu kit de ferramentas emocionais</h2>
            <p class="text-slate-500 dark:text-slate-400">Escolhe o que precisas hoje. Tudo desenhado para ser privado e seguro.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="scroll-reveal group relative bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] dark:hover:shadow-none hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-50 dark:bg-primary-900/20 rounded-bl-[100px] -mr-8 -mt-8 z-0 transition-colors"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-700 border border-primary-100 dark:border-primary-900/50 text-primary-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                        <i class="ri-group-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">A Fogueira</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-6">Salas de áudio e texto temáticas. Entra na sala "Luto" ou "Ansiedade" e percebe que não estás só.</p>
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center text-sm font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700">
                        Ver salas ativas <i class="ri-arrow-right-line ml-1 transition-transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <div class="scroll-reveal group relative bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] dark:hover:shadow-none hover:-translate-y-1 transition-all duration-300 overflow-hidden" style="transition-delay: 100ms">
                <div class="absolute top-0 right-0 w-32 h-32 bg-calm-50 dark:bg-teal-900/20 rounded-bl-[100px] -mr-8 -mt-8 z-0 transition-colors"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-700 border border-teal-100 dark:border-teal-900/50 text-teal-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                        <i class="ri-headphone-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">O Ouvinte</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-6">Às vezes só precisamos de ser ouvidos. Pede um "Buddy" treinado para um chat 1-para-1.</p>
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center text-sm font-bold text-teal-600 dark:text-teal-400 hover:text-teal-700">
                        Pedir conversa <i class="ri-arrow-right-line ml-1 transition-transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <div class="scroll-reveal group relative bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] dark:hover:shadow-none hover:-translate-y-1 transition-all duration-300 overflow-hidden" style="transition-delay: 200ms">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-bl-[100px] -mr-8 -mt-8 z-0 transition-colors"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-700 border border-indigo-100 dark:border-indigo-900/50 text-indigo-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                        <i class="ri-book-open-line"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Diário IA</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-6">Regista o teu dia. A nossa IA deteta padrões de humor e alerta-te se precisares de ajuda extra.</p>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">
                        Abrir meu espaço <i class="ri-arrow-right-line ml-1 transition-transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>