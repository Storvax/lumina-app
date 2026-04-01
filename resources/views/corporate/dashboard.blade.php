<x-lumina-layout title="Portal da Empresa | Lumina Corporate">
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Cabeçalho --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest mb-3 border border-slate-200 dark:border-slate-700">
                        <i class="ri-building-4-line"></i> Dashboard de Recursos Humanos
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                        {{ $company->name }} <i class="ri-verified-badge-fill text-blue-500 text-2xl"></i>
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Dados 100% anónimos e agregados. Nenhuma informação individual é exposta.</p>
                </div>

                {{-- Seletor de período --}}
                <div class="flex items-center gap-2">
                    @foreach([7, 30, 90] as $d)
                        <a href="{{ route('corporate.dashboard', ['dias' => $d]) }}"
                           class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ $selected_days === $d ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50' }}">
                            {{ $d }} dias
                        </a>
                    @endforeach
                </div>
            </div>

            @if($insufficient_data)
                {{-- Dados insuficientes para análise --}}
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-8 text-center">
                    <i class="ri-shield-user-line text-4xl text-amber-400 mb-3 block"></i>
                    <h2 class="text-lg font-bold text-amber-700 dark:text-amber-300 mb-2">Dados insuficientes para análise</h2>
                    <p class="text-amber-600 dark:text-amber-400 text-sm max-w-md mx-auto">
                        Apenas {{ $active_count }} de {{ $total_employees }} colaboradores têm registos neste período.
                        São necessários pelo menos 5 para garantir o anonimato e calcular métricas.
                    </p>
                </div>
            @else
                {{-- Alerta de Burnout --}}
                @if($burnout['percentage'] >= 20)
                    <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-6 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 flex items-center justify-center text-xl shrink-0 animate-pulse">
                                <i class="ri-alarm-warning-fill"></i>
                            </div>
                            <div>
                                <h3 class="text-rose-800 dark:text-rose-300 font-bold text-lg">Alerta de Risco de Burnout</h3>
                                <p class="text-rose-600/80 dark:text-rose-400/80 text-sm mt-1">
                                    {{ $burnout['percentage'] }}% dos colaboradores ativos ({{ $burnout['count'] }} pessoas) apresentam humor consistentemente baixo nos últimos {{ $selected_days }} dias.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- KPIs --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <i class="ri-group-line absolute -right-3 -bottom-3 text-5xl text-slate-50 dark:text-slate-700/50"></i>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Adoção</p>
                        <p class="text-3xl font-black text-slate-800 dark:text-white">{{ $adoption_rate }}%</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $active_count }}/{{ $total_employees }} ativos</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <i class="ri-sun-cloudy-line absolute -right-3 -bottom-3 text-5xl text-slate-50 dark:text-slate-700/50"></i>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Humor Médio</p>
                        @php $avgMood = count($mood_distribution) ? round(collect($mood_distribution)->keys()->map(fn($k,$v=null) => $k * ($mood_distribution[$k] ?? 1))->sum() / max(array_sum($mood_distribution), 1), 2) : null; @endphp
                        <p class="text-3xl font-black text-slate-800 dark:text-white">{{ $avgMood ? number_format($avgMood, 1) : '—' }}<span class="text-base text-slate-400">/5</span></p>
                        <p class="text-xs text-slate-400 mt-1">{{ $selected_days }} dias</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <i class="ri-recycle-line absolute -right-3 -bottom-3 text-5xl text-slate-50 dark:text-slate-700/50"></i>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Taxa de Retorno</p>
                        <p class="text-3xl font-black text-slate-800 dark:text-white">{{ $return_rate }}%</p>
                        <p class="text-xs text-slate-400 mt-1">Uso em 2+ semanas</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                        <i class="ri-alarm-warning-line absolute -right-3 -bottom-3 text-5xl text-slate-50 dark:text-slate-700/50"></i>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Risco Burnout</p>
                        <p class="text-3xl font-black {{ $burnout['percentage'] >= 20 ? 'text-rose-500' : 'text-slate-800 dark:text-white' }}">{{ $burnout['percentage'] }}%</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $burnout['count'] }} colaboradores</p>
                    </div>
                </div>

                {{-- Gráfico de tendência semanal + Benchmark --}}
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <h3 class="font-bold text-slate-700 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="ri-line-chart-line text-indigo-500"></i> Evolução Semanal do Humor
                        </h3>
                        <canvas id="trendChart" height="100"></canvas>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-slate-700 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                                <i class="ri-global-line text-indigo-500"></i> Benchmark de Plataforma
                            </h3>
                            <p class="text-xs text-slate-500 mb-6">Média anónima de todas as empresas na Lumina no mesmo período.</p>
                            @if($benchmark)
                                <div class="text-center py-4">
                                    <p class="text-5xl font-black text-indigo-500">{{ number_format($benchmark, 1) }}<span class="text-xl text-slate-400">/5</span></p>
                                    <p class="text-xs text-slate-400 mt-2 font-bold uppercase tracking-wider">Média de plataforma</p>
                                </div>
                            @else
                                <p class="text-slate-400 text-sm text-center">Sem dados disponíveis.</p>
                            @endif
                        </div>
                        <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs text-slate-500 flex items-start gap-2">
                            <i class="ri-shield-check-fill text-teal-500 text-sm mt-0.5"></i>
                            Dados agregados — a sua empresa não é identificável no benchmark.
                        </div>
                    </div>
                </div>

                {{-- Distribuição de humor + Tags --}}
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <h3 class="font-bold text-slate-700 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="ri-bar-chart-grouped-line text-indigo-500"></i> Distribuição de Humor
                        </h3>
                        @php
                            $moodLabels = [1 => '😢 Muito difícil', 2 => '😔 Difícil', 3 => '😐 Neutro', 4 => '🙂 Bem', 5 => '😊 Muito bem'];
                            $moodColors = [1 => '#f43f5e', 2 => '#f59e0b', 3 => '#94a3b8', 4 => '#14b8a6', 5 => '#6366f1'];
                            $totalLogs = array_sum($mood_distribution);
                        @endphp
                        <div class="space-y-3">
                            @for($level = 5; $level >= 1; $level--)
                                @php
                                    $count = $mood_distribution[$level] ?? 0;
                                    $pct   = $totalLogs > 0 ? round(($count / $totalLogs) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1">
                                        <span>{{ $moodLabels[$level] }}</span>
                                        <span class="text-slate-500">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full transition-all" style="width: {{ $pct }}%; background: {{ $moodColors[$level] }}"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <h3 class="font-bold text-slate-700 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="ri-price-tag-3-line text-indigo-500"></i> Temas Predominantes
                        </h3>
                        @if(count($top_tags) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($top_tags as $tag => $count)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-bold">
                                        {{ $tag }} <span class="bg-indigo-100 dark:bg-indigo-800 text-xs px-1.5 py-0.5 rounded-full">{{ $count }}×</span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400 text-sm">Nenhuma tag registada neste período.</p>
                        @endif
                    </div>
                </div>

                {{-- Nota de privacidade --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 text-sm text-slate-500 flex items-start gap-3">
                    <i class="ri-shield-check-fill text-teal-500 text-lg mt-0.5"></i>
                    <p>Os dados são exibidos apenas quando existem ≥5 colaboradores ativos no período, garantindo o anonimato total. Nenhuma informação individual é acessível pelos gestores de RH.</p>
                </div>
            @endif

        </div>
    </div>

    @if(!$insufficient_data)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const isDark   = document.documentElement.classList.contains('dark');
                const grid     = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
                const tick     = isDark ? '#94a3b8' : '#64748b';

                new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: @json($mood_trend_weekly['labels']),
                        datasets: [{
                            label: 'Humor médio',
                            data: @json($mood_trend_weekly['values']),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.08)',
                            pointBackgroundColor: '#6366f1',
                            pointRadius: 5,
                            tension: 0.4,
                            fill: true,
                            spanGaps: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { min: 1, max: 5, ticks: { stepSize: 1, color: tick }, grid: { color: grid } },
                            x: { ticks: { color: tick, maxRotation: 45, autoSkip: true }, grid: { color: grid } }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ctx.raw ? `Média: ${ctx.raw}/5` : 'Sem dados' } }
                        }
                    }
                });
            });
        </script>
    @endif
</x-lumina-layout>
