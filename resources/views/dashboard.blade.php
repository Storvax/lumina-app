<x-lumina-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Olá, {{ Auth::user()->name }} 👋</h1>
                <p class="text-slate-500 dark:text-slate-400">O que queres fazer pelo teu bem-estar hoje?</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <a href="{{ route('rooms.index') }}" class="group relative bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm"><i class="ri-group-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Salas de Apoio</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Entra numa sala, ouve, partilha ou simplesmente está presente.</p>
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Entrar agora <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('diary.index') }}" class="group relative bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 dark:bg-teal-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm"><i class="ri-book-heart-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Diário Emocional</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Despeja os teus pensamentos. Ninguém vai ler a não ser tu.</p>
                        <span class="text-teal-600 dark:text-teal-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Escrever <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('profile.show') }}" class="group relative bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm"><i class="ri-fire-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Minha Fogueira</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Vê o teu progresso, chamas acumuladas e conquistas.</p>
                        <span class="text-orange-600 dark:text-orange-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Ver Santuário <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

            </div>

            <div class="mt-12">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Para hoje</h2>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 space-y-4">
                    <div class="flex items-center gap-4 group">
                        <div class="w-6 h-6 rounded-full border-2 border-slate-200 dark:border-slate-600 flex items-center justify-center group-hover:border-green-500 transition-colors cursor-pointer">
                            <div class="w-3 h-3 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                        <span class="text-slate-600 dark:text-slate-300 text-sm line-through decoration-slate-300">Entrar na app (Feito!)</span>
                    </div>
                    <div class="flex items-center gap-4 group cursor-pointer" onclick="window.location='{{ route('diary.index') }}'">
                        <div class="w-6 h-6 rounded-full border-2 border-slate-200 dark:border-slate-600 flex items-center justify-center group-hover:border-primary-500 transition-colors"></div>
                        <span class="text-slate-600 dark:text-slate-300 text-sm group-hover:text-primary-500">Escrever como me sinto</span>
                        <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold">+5 🔥</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-lumina-layout>