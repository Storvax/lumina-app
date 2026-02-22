<x-lumina-layout title="Dashboard | Lumina">
    @php
        // Extrai a tag emocional principal do utilizador (se existir)
        $tags = Auth::user()->emotional_tags ?? [];
        $primaryMood = count($tags) > 0 ? strtolower($tags[0]) : 'neutro';

        // Mapeamento Psicológico de Cores (Com suporte a Dark Mode)
        $moodGradient = match(true) {
            str_contains($primaryMood, 'ansiedade') => 'from-amber-50/80 via-orange-50/30 to-transparent dark:from-amber-900/20 dark:via-orange-900/10',
            str_contains($primaryMood, 'tristeza') => 'from-emerald-50/80 via-teal-50/30 to-transparent dark:from-emerald-900/20 dark:via-teal-900/10',
            str_contains($primaryMood, 'sobrecarregado') => 'from-blue-50/80 via-indigo-50/30 to-transparent dark:from-blue-900/20 dark:via-indigo-900/10',
            default => 'from-indigo-50/50 via-violet-50/20 to-transparent dark:from-indigo-900/20 dark:via-violet-900/10'
        };
    @endphp

    <div class="fixed inset-0 bg-gradient-to-b {{ $moodGradient }} -z-10 transition-colors duration-1000"></div>

    <div class="py-12 pt-28 md:pt-32 relative z-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Olá, {{ explode(' ', trim(Auth::user()->name))[0] }} 👋</h1>
                <p class="text-slate-500 dark:text-slate-400">O que queres fazer pelo teu bem-estar hoje?</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <a href="{{ route('rooms.index') }}" class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm"><i class="ri-group-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Salas de Apoio</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Entra numa sala, ouve, partilha ou simplesmente está presente.</p>
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Entrar agora <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('diary.index') }}" class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 dark:bg-teal-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm"><i class="ri-book-heart-line"></i></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Diário Emocional</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Despeja os teus pensamentos. Ninguém vai ler a não ser tu.</p>
                        <span class="text-teal-600 dark:text-teal-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">Escrever <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>

                <a href="{{ route('profile.show') }}" class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
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
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-8 -mt-8"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                            <div>
                                <h2 class="font-bold text-xl text-slate-800 dark:text-white flex items-center gap-2">
                                    <i class="ri-focus-2-line text-orange-500"></i> Foco de Hoje
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pequenos passos para cuidares de ti.</p>
                            </div>
                            <span class="text-xs font-bold text-orange-500 bg-orange-50 dark:bg-orange-900/30 px-3 py-1 rounded-full border border-orange-100 dark:border-orange-800">Renova à meia-noite</span>
                        </div>

                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @if(isset($dailyMissions))
                                @forelse($dailyMissions as $mission)
                                    @php 
                                        $isCompleted = !is_null($mission->pivot->completed_at);
                                        $progressPercent = min(100, ($mission->pivot->progress / $mission->target_count) * 100);
                                    @endphp

                                    <div class="p-5 rounded-2xl border transition-all {{ $isCompleted ? 'bg-orange-50/50 dark:bg-orange-900/10 border-orange-100 dark:border-orange-800 opacity-80' : 'bg-slate-50 dark:bg-slate-700/50 border-slate-100 dark:border-slate-600 hover:border-orange-200 dark:hover:border-orange-700' }}">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex items-start gap-3">
                                                <div class="w-6 h-6 mt-0.5 rounded-full flex items-center justify-center shrink-0 border-2 transition-colors {{ $isCompleted ? 'bg-orange-500 border-orange-500 text-white' : 'border-slate-300 dark:border-slate-500 text-transparent' }}">
                                                    <i class="ri-check-line text-xs font-bold"></i>
                                                </div>
                                                
                                                <div>
                                                    <p class="text-sm font-bold leading-tight {{ $isCompleted ? 'text-orange-800 dark:text-orange-400 line-through' : 'text-slate-800 dark:text-white' }}">{{ $mission->title }}</p>
                                                    @if(!$isCompleted)
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">{{ $mission->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <span class="text-xs font-bold flex items-center gap-1 shrink-0 ml-2 {{ $isCompleted ? 'text-orange-500' : 'text-slate-400 dark:text-slate-500' }}">
                                                <i class="ri-fire-fill"></i> {{ $mission->flames_reward }}
                                            </span>
                                        </div>

                                        @if(!$isCompleted)
                                            <div class="ml-9 h-1.5 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden mt-3">
                                                <div class="h-full bg-orange-400 transition-all duration-1000 ease-out" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                            <p class="ml-9 text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider">{{ $mission->pivot->progress }} / {{ $mission->target_count }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <div class="col-span-full text-center py-8 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                                        <i class="ri-leaf-line text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Sem missões para hoje. Tira o dia para ti e descansa!</p>
                                    </div>
                                @endforelse
                            @else
                                <div class="col-span-full text-center py-6">
                                    <p class="text-sm text-slate-500">A carregar os teus objetivos...</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-lumina-layout>