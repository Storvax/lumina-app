<x-lumina-layout title="Impacto Comunitário | Lumina">
    <div class="py-12 pt-32">
        <div class="max-w-4xl mx-auto px-6">

            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-4">
                    Relatório Mensal
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-4">
                    O Impacto da <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-600">Nossa Comunidade</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 max-w-lg mx-auto">
                    Números anónimos que contam uma história de cuidado mútuo. Últimos 30 dias.
                </p>
            </div>

            {{-- Estatísticas principais --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($stats['total_members']) }}</p>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">Membros</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($stats['active_members']) }}</p>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">Ativos esta semana</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-3xl font-black text-violet-600 dark:text-violet-400">{{ number_format($stats['diary_entries']) }}</p>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">Registos no diário</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ $stats['avg_mood'] }}/5</p>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">Humor médio</p>
                </div>
            </div>

            {{-- Destaques --}}
            <div class="grid sm:grid-cols-3 gap-6 mb-12">
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl p-6 border border-emerald-100 dark:border-emerald-800">
                    <i class="ri-seedling-line text-2xl text-emerald-500 mb-3 block"></i>
                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ $stats['hope_posts'] }}</p>
                    <p class="text-sm text-emerald-600/80 dark:text-emerald-400/70 mt-1">Histórias de esperança partilhadas</p>
                </div>
                <div class="bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 rounded-2xl p-6 border border-rose-100 dark:border-rose-800">
                    <i class="ri-heart-3-line text-2xl text-rose-500 mb-3 block"></i>
                    <p class="text-2xl font-black text-rose-700 dark:text-rose-400">{{ number_format($stats['reactions_given']) }}</p>
                    <p class="text-sm text-rose-600/80 dark:text-rose-400/70 mt-1">Abraços e velas trocados</p>
                </div>
                <div class="bg-gradient-to-br from-violet-50 to-indigo-50 dark:from-violet-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-violet-100 dark:border-violet-800">
                    <i class="ri-hand-heart-line text-2xl text-violet-500 mb-3 block"></i>
                    <p class="text-2xl font-black text-violet-700 dark:text-violet-400">{{ $stats['buddy_sessions'] }}</p>
                    <p class="text-sm text-violet-600/80 dark:text-violet-400/70 mt-1">Sessões de escuta completadas</p>
                </div>
            </div>

            {{-- Nota de privacidade --}}
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 text-center">
                <i class="ri-shield-check-line text-slate-400 text-xl mb-2 block"></i>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Todos os dados são completamente anónimos e agregados. A privacidade dos nossos membros é inegociável.
                </p>
            </div>
        </div>
    </div>
</x-lumina-layout>
