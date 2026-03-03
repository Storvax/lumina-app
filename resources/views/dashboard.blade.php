<x-lumina-layout title="Dashboard | Lumina">
    @php
        /*
         * Gradiente de fundo baseado no mood_level (1-5) do registo de hoje.
         * Mapa cromático: 1 (cinza-azulado) → 2 (azul frio) → 3 (neutro) → 4 (verde) → 5 (amarelo-quente).
         * Fallback para tags emocionais quando não há registo.
         */
        $moodLevel = $progressData['todayMoodLevel'] ?? null;

        $moodGradient = match($moodLevel) {
            1 => 'from-slate-100/80 via-blue-50/40 to-transparent dark:from-slate-800/30 dark:via-blue-900/15',
            2 => 'from-blue-50/80 via-indigo-50/30 to-transparent dark:from-blue-900/20 dark:via-indigo-900/10',
            3 => 'from-indigo-50/50 via-violet-50/20 to-transparent dark:from-indigo-900/20 dark:via-violet-900/10',
            4 => 'from-emerald-50/80 via-teal-50/30 to-transparent dark:from-emerald-900/20 dark:via-teal-900/10',
            5 => 'from-amber-50/80 via-yellow-50/30 to-transparent dark:from-amber-900/20 dark:via-yellow-900/10',
            default => null,
        };

        // Fallback: se não há mood_level, usar tags emocionais
        if (!$moodGradient) {
            $primaryMood = strtolower($emotionalTags[0] ?? 'neutro');
            $moodGradient = match(true) {
                str_contains($primaryMood, 'ansiedade')      => 'from-amber-50/80 via-orange-50/30 to-transparent dark:from-amber-900/20 dark:via-orange-900/10',
                str_contains($primaryMood, 'tristeza')       => 'from-emerald-50/80 via-teal-50/30 to-transparent dark:from-emerald-900/20 dark:via-teal-900/10',
                str_contains($primaryMood, 'sobrecarregado') => 'from-blue-50/80 via-indigo-50/30 to-transparent dark:from-blue-900/20 dark:via-indigo-900/10',
                default                                      => 'from-indigo-50/50 via-violet-50/20 to-transparent dark:from-indigo-900/20 dark:via-violet-900/10',
            };
        }

        /*
         * Configuração visual do nível de fogueira do utilizador.
         * Cada nível tem cor, ícone e etiqueta próprios — sem hierarquia punitiva,
         * apenas celebração do percurso individual.
         */
        $levelConfig = match($progressData['level'] ?? 'spark') {
            'spark'   => ['icon' => 'ri-sparkling-line', 'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',  'ring' => 'ring-yellow-200 dark:ring-yellow-800',  'label' => 'Faísca'],
            'flame'   => ['icon' => 'ri-fire-line',      'color' => 'text-orange-500', 'bg' => 'bg-orange-50 dark:bg-orange-900/20',  'ring' => 'ring-orange-200 dark:ring-orange-800',  'label' => 'Chama'],
            'bonfire' => ['icon' => 'ri-fire-fill',      'color' => 'text-rose-500',   'bg' => 'bg-rose-50 dark:bg-rose-900/20',      'ring' => 'ring-rose-200 dark:ring-rose-800',      'label' => 'Fogueira'],
            'beacon'  => ['icon' => 'ri-sun-fill',       'color' => 'text-amber-500',  'bg' => 'bg-amber-50 dark:bg-amber-900/20',    'ring' => 'ring-amber-200 dark:ring-amber-800',    'label' => 'Farol'],
            default   => ['icon' => 'ri-sparkling-line', 'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',  'ring' => 'ring-yellow-200 dark:ring-yellow-800',  'label' => 'Faísca'],
        };

        /*
         * Sugestão contextual baseada na tag emocional principal.
         * O recurso mais relevante para o estado actual é destacado
         * em vez de apresentar sempre os módulos pela mesma ordem.
         */
        $contextualHint = match(true) {
            str_contains($primaryMood, 'ansiedade')      => ['icon' => 'ri-lungs-line',     'color' => 'text-amber-600 dark:text-amber-400',   'text' => 'A respiração consciente pode ajudar agora. Experimenta uma pausa de 2 minutos.'],
            str_contains($primaryMood, 'tristeza')       => ['icon' => 'ri-book-read-line', 'color' => 'text-teal-600 dark:text-teal-400',     'text' => 'O teu diário está à espera. Às vezes escrever alivia o que as palavras não conseguem dizer.'],
            str_contains($primaryMood, 'sobrecarregado') => ['icon' => 'ri-group-line',     'color' => 'text-indigo-600 dark:text-indigo-400', 'text' => 'Não tens de carregar isto sozinho. Há pessoas nas salas que entendem.'],
            default                                      => null,
        };

        // Primeiro nome do utilizador para saudação mais próxima
        $firstName = explode(' ', trim(Auth::user()->name))[0];
    @endphp

    {{-- Gradiente de fundo emocional — fixo, transição suave ao mudar de estado --}}
    <div class="fixed inset-0 bg-gradient-to-b {{ $moodGradient }} -z-10 transition-colors duration-1000"></div>

    <div class="py-12 pt-28 md:pt-32 relative z-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-8">

            {{-- ================================================================
                 SECÇÃO 1: SAUDAÇÃO CONTEXTUAL + MINI PAINEL DE PROGRESSO
                 A hora do dia e o estado do utilizador definem o tom da saudação.
                 O painel de streak/chamas/nível traz o progresso para o topo
                 da página, eliminando a necessidade de navegar para o perfil.
                 ================================================================ --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-400 dark:text-slate-500 mb-1 tracking-wide uppercase">
                        {{ now()->isoFormat('dddd, D [de] MMMM') }}
                    </p>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
                        {{ $greeting }}, {{ $firstName }} 👋
                    </h1>
                    {{-- Frase de encorajamento rotativa — muda uma vez por dia, não a cada reload --}}
                    <p class="text-slate-500 dark:text-slate-400 mt-1.5 text-sm italic">
                        "{{ $encouragement }}"
                    </p>
                </div>

                {{-- Mini painel: streak + chamas + nível --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center gap-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl px-4 py-2.5 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <i class="ri-fire-fill text-orange-500 text-lg"></i>
                        <div class="leading-tight">
                            <p class="text-base font-bold text-slate-900 dark:text-white">{{ $progressData['streak'] }}</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ $progressData['streak'] === 1 ? 'dia' : 'dias' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl px-4 py-2.5 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <i class="{{ $levelConfig['icon'] }} {{ $levelConfig['color'] }} text-lg"></i>
                        <div class="leading-tight">
                            <p class="text-base font-bold text-slate-900 dark:text-white">{{ number_format($progressData['flames']) }}</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">chamas</p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-1.5 {{ $levelConfig['bg'] }} {{ $levelConfig['ring'] }} ring-1 rounded-2xl px-3 py-2.5">
                        <i class="{{ $levelConfig['icon'] }} {{ $levelConfig['color'] }}"></i>
                        <span class="text-xs font-bold {{ $levelConfig['color'] }}">{{ $levelConfig['label'] }}</span>
                    </div>
                </div>
            </div>

            {{-- ================================================================
                 SECÇÃO 2: CHECK-IN EMOCIONAL RÁPIDO
                 Só aparece se o utilizador ainda não fez o registo de hoje.
                 Apresentado de forma suave — nunca obrigatório nem culpabilizante.
                 ================================================================ --}}
            @if(!$progressData['todayLogged'])
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center shrink-0">
                            <i class="ri-emotion-line text-teal-500 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-800 dark:text-white text-sm">Como estás hoje?</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Um registo rápido ajuda-te a perceber os teus padrões ao longo do tempo.
                            </p>
                        </div>
                        <a href="{{ route('diary.index') }}"
                           class="shrink-0 text-xs font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-4 py-2 rounded-full hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-colors whitespace-nowrap">
                            Registar agora
                        </a>
                    </div>
                </div>
            @endif

            {{-- ================================================================
                 SECÇÃO 3: SUGESTÃO CONTEXTUAL (baseada no estado emocional)
                 Só aparece quando existe uma tag emocional relevante declarada.
                 Orienta sem pressionar — oferece, não impõe.
                 ================================================================ --}}
            @if($contextualHint)
                <div class="flex items-start gap-3 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl px-5 py-4 border border-slate-100 dark:border-slate-700">
                    <i class="{{ $contextualHint['icon'] }} {{ $contextualHint['color'] }} text-xl mt-0.5 shrink-0"></i>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ $contextualHint['text'] }}
                    </p>
                </div>
            @endif

            {{-- ================================================================
                 SECÇÃO 3.5: CALENDÁRIO EMOCIONAL PORTUGUÊS
                 Card contextual em datas com impacto emocional cultural.
                 ================================================================ --}}
            @if(!empty($emotionalDate))
                @php
                    $dateTypeStyles = match($emotionalDate['type'] ?? 'awareness') {
                        'grief'       => 'bg-slate-50/80 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700',
                        'celebration' => 'bg-amber-50/80 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800',
                        'family'      => 'bg-rose-50/80 dark:bg-rose-900/20 border-rose-200 dark:border-rose-800',
                        default       => 'bg-teal-50/80 dark:bg-teal-900/20 border-teal-200 dark:border-teal-800',
                    };
                    $dateIconColor = match($emotionalDate['type'] ?? 'awareness') {
                        'grief'       => 'text-slate-500 dark:text-slate-400',
                        'celebration' => 'text-amber-500 dark:text-amber-400',
                        'family'      => 'text-rose-500 dark:text-rose-400',
                        default       => 'text-teal-500 dark:text-teal-400',
                    };
                @endphp
                <div class="flex items-start gap-4 {{ $dateTypeStyles }} backdrop-blur-sm rounded-2xl px-5 py-4 border">
                    <i class="{{ $emotionalDate['icon'] }} {{ $dateIconColor }} text-2xl mt-0.5 shrink-0"></i>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider {{ $dateIconColor }} mb-1">{{ $emotionalDate['title'] }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $emotionalDate['message'] }}</p>
                    </div>
                </div>
            @endif

            {{-- ================================================================
                 SECÇÃO 4: CARTÕES DE ACESSO RÁPIDO AOS MÓDULOS PRINCIPAIS
                 Funcionalidade original preservada e enriquecida.
                 O cartão do Diário adapta o CTA conforme o utilizador já registou hoje.
                 ================================================================ --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

                <a href="{{ route('rooms.index') }}"
                   class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm">
                            <i class="ri-group-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Salas de Apoio</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Entra numa sala, ouve, partilha ou simplesmente está presente.</p>
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                            Entrar agora <i class="ri-arrow-right-line"></i>
                        </span>
                    </div>
                </a>

                <a href="{{ route('diary.index') }}"
                   class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 dark:bg-teal-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm">
                            <i class="ri-book-read-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Diário Emocional</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Despeja os teus pensamentos. Ninguém vai ler a não ser tu.</p>
                        <span class="text-teal-600 dark:text-teal-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                            {{-- CTA adapta-se ao estado de registo de hoje --}}
                            {{ $progressData['todayLogged'] ? 'Ver registo de hoje' : 'Escrever' }}
                            <i class="ri-arrow-right-line"></i>
                        </span>
                    </div>
                </a>

                <a href="{{ route('profile.show') }}"
                   class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-300 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-sm">
                            <i class="ri-fire-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Minha Fogueira</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Vê o teu progresso, chamas acumuladas e conquistas.</p>
                        <span class="text-orange-600 dark:text-orange-400 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                            Ver Santuário <i class="ri-arrow-right-line"></i>
                        </span>
                    </div>
                </a>

            </div>

            {{-- SECÇÃO 4.5: CALOR DA COMUNIDADE (O NOVO WIDGET) --}}
            @php
                // Prevenção de erros enquanto o backend não é atualizado pelo Claude
                $cCurrent = $communityStats['current'] ?? 850;
                $cTarget = $communityStats['target'] ?? 1000;
                $cPercentage = $communityStats['percentage'] ?? 85;
            @endphp
            
            <div class="relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-[2rem] p-6 md:p-8 border border-rose-100 dark:border-rose-900/30 shadow-sm overflow-hidden group mt-4">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-gradient-to-br from-amber-100 to-rose-100 dark:from-rose-900/20 dark:to-amber-900/20 rounded-full blur-3xl opacity-70 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-gradient-to-tr from-rose-50 to-indigo-50 dark:from-indigo-900/20 dark:to-rose-900/20 rounded-full blur-2xl opacity-60 pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-rose-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-rose-500/20 animate-[bounceSlight_3s_ease-in-out_infinite]">
                                <i class="ri-fire-fill"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-800 dark:text-white">O Calor da Comunidade</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Apoio partilhado esta semana</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('forum.index') }}" class="hidden md:flex items-center gap-2 text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 px-4 py-2 rounded-xl hover:bg-rose-100 transition-colors">
                            Contribuir <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>

                    <div class="mt-6 relative">
                        <div class="flex justify-between text-xs font-bold mb-2">
                            <span class="text-rose-600 dark:text-rose-400">
                                <i class="ri-heart-pulse-fill"></i> <span>{{ $cCurrent }}</span> interações
                            </span>
                            <span class="text-slate-400">
                                Objetivo: {{ $cTarget }}
                            </span>
                        </div>
                        
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-5 md:h-6 overflow-hidden shadow-inner relative">
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-400 via-orange-400 to-rose-500 relative transition-all duration-1500 ease-out" 
                                 style="width: {{ $cPercentage }}%;">
                                <div class="absolute top-0 left-0 right-0 bottom-0 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.4),transparent)] animate-[shimmer_2s_infinite] -translate-x-full"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-4 font-medium">
                        Cada abraço 🫂, vela 🕯️ e comentário conta. Juntos, criamos um espaço mais seguro.
                    </p>
                </div>
            </div>

            {{-- ================================================================
                 SECÇÃO 5: BANNER DE MARCO — DADOS REAIS APENAS
                 Bug corrigido: o "|| true" foi removido.
                 O banner só aparece quando $pendingMilestone tem dados reais
                 vindos do DashboardController::detectPendingMilestone().
                 ================================================================ --}}
            @if($pendingMilestone)
                <div x-data="{ open: false }"
                     class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/10 rounded-3xl p-6 shadow-sm border border-amber-100 dark:border-amber-900/50">

                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-full shadow-sm shrink-0"
                             :class="{ 'animate-pulse': !open }">
                            <span class="text-2xl leading-none">{{ $pendingMilestone['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                                {{ $pendingMilestone['title'] }}
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ $pendingMilestone['description'] }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <button @click="open = !open"
                                class="px-5 py-2.5 bg-amber-500 text-white rounded-full text-sm font-bold hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-500 transition-colors shadow-sm focus:ring-2 ring-amber-300 ring-offset-1 dark:ring-offset-slate-800">
                            Partilhar este Marco
                        </button>
                        <span class="text-xs text-slate-400 dark:text-slate-500 italic">
                            Podes partilhar na Fogueira, se quiseres.
                        </span>
                    </div>

                    <div x-show="open" x-collapse x-cloak class="mt-4">
                        <form action="{{ route('forum.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="milestone_type" value="{{ $progressData['streak'] }}_days_streak">
                            <textarea
                                name="custom_message"
                                rows="2"
                                class="w-full rounded-xl border-amber-200 dark:border-amber-800/50 bg-white/50 dark:bg-slate-800/50 text-slate-800 dark:text-white focus:ring-amber-500 focus:border-amber-500 text-sm p-4 placeholder-slate-400 dark:placeholder-slate-500 resize-none transition-colors"
                                placeholder="Adiciona os teus sentimentos (opcional)..."
                            ></textarea>
                            <button type="submit"
                                    class="mt-3 w-full py-3 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-sm font-bold hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors shadow-sm">
                                Publicar na Fogueira
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- ================================================================
                 SECÇÃO 6: FOCO DE HOJE — MISSÕES DIÁRIAS
                 Melhorias: barra de progresso global, celebração de conclusão,
                 estado vazio mais humano, mensagem "Feito. Bem feito." por missão.
                 Bug corrigido: divisão por zero protegida com max(1, target_count).
                 ================================================================ --}}
            <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-3xl p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>

                <div class="relative z-10">

                    @php
                        $totalMissions     = $dailyMissions->count();
                        $completedMissions = $dailyMissions->filter(fn($m) => !is_null($m->pivot->completed_at))->count();
                        $allDone           = $totalMissions > 0 && $completedMissions === $totalMissions;
                    @endphp

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                        <div>
                            <h2 class="font-bold text-xl text-slate-800 dark:text-white flex items-center gap-2">
                                <i class="ri-focus-2-line text-orange-500"></i> Foco de Hoje
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pequenos passos para cuidares de ti.</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if($totalMissions > 0)
                                <span class="text-xs font-bold {{ $allDone ? 'text-green-600 dark:text-green-400' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $completedMissions }}/{{ $totalMissions }}
                                    @if($allDone) <i class="ri-checkbox-circle-fill ml-0.5"></i> @endif
                                </span>
                            @endif
                            <span class="text-xs font-bold text-orange-500 bg-orange-50 dark:bg-orange-900/30 px-3 py-1 rounded-full border border-orange-100 dark:border-orange-800">
                                Renova à meia-noite
                            </span>
                        </div>
                    </div>

                    {{-- Barra de progresso global das missões do dia --}}
                    @if($totalMissions > 0)
                        <div class="mb-6 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700 ease-out {{ $allDone ? 'bg-green-400' : 'bg-orange-400' }}"
                                 style="width: {{ round(($completedMissions / $totalMissions) * 100) }}%">
                            </div>
                        </div>
                    @endif

                    {{-- Mensagem celebratória quando todas as missões estão concluídas --}}
                    @if($allDone)
                        <div class="mb-6 flex items-center gap-3 bg-green-50 dark:bg-green-900/20 rounded-2xl px-5 py-4 border border-green-100 dark:border-green-800">
                            <i class="ri-checkbox-circle-fill text-green-500 text-2xl shrink-0"></i>
                            <div>
                                <p class="font-bold text-green-800 dark:text-green-300 text-sm">Completaste o teu foco de hoje!</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                    Isso não é pouco. Podes descansar com a consciência tranquila.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if(isset($dailyMissions))
                            @forelse($dailyMissions as $mission)
                                @php
                                    $isCompleted = !is_null($mission->pivot->completed_at);

                                    /*
                                     * Protecção contra divisão por zero:
                                     * target_count pode ser 0 em missões criadas sem valor explícito.
                                     */
                                    $targetCount     = max(1, (int) $mission->target_count);
                                    $progressPercent = min(100, (int) round(($mission->pivot->progress / $targetCount) * 100));
                                @endphp

                                <div class="p-5 rounded-2xl border transition-all duration-300
                                    {{ $isCompleted
                                        ? 'bg-green-50/60 dark:bg-green-900/10 border-green-100 dark:border-green-800'
                                        : 'bg-slate-50 dark:bg-slate-700/50 border-slate-100 dark:border-slate-600 hover:border-orange-200 dark:hover:border-orange-700 hover:shadow-sm'
                                    }}">

                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-start gap-3">
                                            {{-- Check verde ao concluir — reforço positivo visual --}}
                                            <div class="w-6 h-6 mt-0.5 rounded-full flex items-center justify-center shrink-0 border-2 transition-all duration-300
                                                {{ $isCompleted
                                                    ? 'bg-green-500 border-green-500 text-white'
                                                    : 'border-slate-300 dark:border-slate-500 text-transparent'
                                                }}">
                                                <i class="ri-check-line text-xs font-bold"></i>
                                            </div>

                                            <div>
                                                <p class="text-sm font-bold leading-tight
                                                    {{ $isCompleted
                                                        ? 'text-green-800 dark:text-green-400 line-through decoration-green-400/60'
                                                        : 'text-slate-800 dark:text-white'
                                                    }}">
                                                    {{ $mission->title }}
                                                </p>
                                                @if(!$isCompleted && $mission->description)
                                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">
                                                        {{ $mission->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="text-xs font-bold flex items-center gap-1 shrink-0 ml-2
                                            {{ $isCompleted ? 'text-green-500' : 'text-slate-400 dark:text-slate-500' }}">
                                            <i class="ri-fire-fill"></i> +{{ $mission->flames_reward }}
                                        </span>
                                    </div>

                                    @if(!$isCompleted)
                                        <div class="ml-9 mt-3">
                                            <div class="h-1.5 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                                                <div class="h-full bg-orange-400 transition-all duration-700 ease-out"
                                                     style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-1.5 uppercase tracking-wider">
                                                {{ $mission->pivot->progress }} / {{ $targetCount }}
                                            </p>
                                        </div>
                                    @else
                                        {{-- Mensagem humana de conclusão em vez de silêncio --}}
                                        <p class="ml-9 text-[10px] text-green-600 dark:text-green-400 mt-1.5 font-medium">
                                            Feito. Bem feito.
                                        </p>
                                    @endif
                                </div>

                            @empty
                                {{-- Estado vazio: acolhedor, sem pressão, sem vazio frio --}}
                                <div class="col-span-full flex flex-col items-center py-10 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                                    <i class="ri-leaf-line text-4xl text-slate-300 dark:text-slate-600 mb-3"></i>
                                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Sem missões para hoje.</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 text-center max-w-xs">
                                        Tira o dia para ti. Descansar também é cuidar de si.
                                    </p>
                                </div>
                            @endforelse
                        @else
                            <div class="col-span-full text-center py-8">
                                <p class="text-sm text-slate-400 dark:text-slate-500">A preparar o teu foco...</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-lumina-layout>