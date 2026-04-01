<x-lumina-layout title="Tendências de Humor | Lumina">
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">

            {{-- Cabeçalho --}}
            <div class="mb-8">
                <a href="{{ route('profile.show') }}" class="text-sm text-slate-400 hover:text-indigo-500 flex items-center gap-1 mb-3 transition-colors">
                    <i class="ri-arrow-left-s-line"></i> Voltar ao Perfil
                </a>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                    <i class="ri-line-chart-line text-indigo-500"></i> As Tuas Tendências de Humor
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Análise baseada nos teus registos do diário emocional.</p>
            </div>

            {{-- Alerta Proativo --}}
            @if($data['alert'])
                <div class="mb-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-10 h-10 bg-rose-100 dark:bg-rose-800/40 text-rose-500 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                        <i class="ri-heart-pulse-line"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-rose-700 dark:text-rose-300 text-sm">{{ $data['alert']['message'] }}</p>
                        <p class="text-rose-500 dark:text-rose-400 text-xs mt-0.5">Estamos aqui para ti. Considera falar com alguém de confiança.</p>
                    </div>
                    <a href="{{ route($data['alert']['route']) }}"
                       class="flex-shrink-0 bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm px-4 py-2 rounded-xl transition-colors">
                        {{ $data['alert']['cta'] }}
                    </a>
                </div>
            @endif

            {{-- Tabs de período --}}
            <div x-data="{ period: '30' }" class="space-y-8">
                <div class="flex gap-2 bg-white dark:bg-slate-800 p-1.5 rounded-2xl border border-slate-100 dark:border-slate-700 w-fit shadow-sm">
                    @foreach([['7', '7 dias'], ['30', '30 dias'], ['90', '90 dias']] as [$val, $label])
                        <button @click="period = '{{ $val }}'"
                                :class="period === '{{ $val }}' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-white'"
                                class="px-4 py-2 rounded-xl text-sm font-bold transition-all">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Gráfico de linha --}}
                @foreach(['7', '30', '90'] as $days)
                    @php $p = $data["period_{$days}"]; @endphp
                    <div x-show="period === '{{ $days }}'" x-transition.opacity class="space-y-6">

                        {{-- Resumo estatístico --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                                <p class="text-2xl font-black text-slate-800 dark:text-white">
                                    {{ $p['average'] ? number_format($p['average'], 1) : '—' }}<span class="text-sm text-slate-400">/5</span>
                                </p>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Média</p>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                                <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $p['filled_days'] }}</p>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Dias Registados</p>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                                <p class="text-2xl font-black {{ $p['filled_days'] > 0 ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">
                                    {{ $days > 0 ? number_format(($p['filled_days'] / $days) * 100, 0) : '0' }}%
                                </p>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Consistência</p>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                                @php
                                    $trendIcon = match($p['trend']) {
                                        'improving' => ['ri-arrow-up-line', 'text-teal-500', 'A Melhorar'],
                                        'declining' => ['ri-arrow-down-line', 'text-rose-400', 'Em Queda'],
                                        'stable'    => ['ri-subtract-line', 'text-amber-400', 'Estável'],
                                        default     => ['ri-question-line', 'text-slate-400', 'Sem Dados'],
                                    };
                                @endphp
                                <p class="text-2xl font-black {{ $trendIcon[1] }}"><i class="{{ $trendIcon[0] }}"></i></p>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $trendIcon[2] }}</p>
                            </div>
                        </div>

                        {{-- Gráfico Chart.js --}}
                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                            <canvas id="chart-{{ $days }}" height="100"></canvas>
                        </div>

                        {{-- Legenda dos níveis de humor --}}
                        <div class="flex flex-wrap gap-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            @foreach([['#f43f5e','1 — Muito difícil'],['#f59e0b','2 — Difícil'],['#94a3b8','3 — Neutro'],['#14b8a6','4 — Bem'],['#6366f1','5 — Muito bem']] as [$color, $label])
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $color }}"></span>
                                    {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const labelColor = isDark ? '#94a3b8' : '#64748b';

            const moodColors = {
                1: '#f43f5e', 2: '#f59e0b', 3: '#94a3b8', 4: '#14b8a6', 5: '#6366f1'
            };

            const datasets = @json([
                '7'  => ['labels' => $data['period_7']['labels'],  'values' => $data['period_7']['values'],  'moving_avg' => $data['period_7']['moving_avg']],
                '30' => ['labels' => $data['period_30']['labels'], 'values' => $data['period_30']['values'], 'moving_avg' => $data['period_30']['moving_avg']],
                '90' => ['labels' => $data['period_90']['labels'], 'values' => $data['period_90']['values'], 'moving_avg' => $data['period_90']['moving_avg']],
            ]);

            Object.entries(datasets).forEach(([days, d]) => {
                const ctx = document.getElementById(`chart-${days}`);
                if (!ctx) return;

                // Cor de cada ponto baseada no nível de humor
                const pointColors = d.values.map(v => v ? (moodColors[v] || '#6366f1') : 'transparent');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: d.labels,
                        datasets: [
                            {
                                label: 'Humor',
                                data: d.values,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,0.08)',
                                pointBackgroundColor: pointColors,
                                pointBorderColor: pointColors,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                tension: 0.4,
                                fill: true,
                                spanGaps: true,
                            },
                            {
                                label: 'Média Móvel (3 dias)',
                                data: d.moving_avg,
                                borderColor: '#14b8a6',
                                borderDash: [6, 3],
                                pointRadius: 0,
                                tension: 0.4,
                                fill: false,
                                spanGaps: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: {
                                min: 1, max: 5,
                                ticks: {
                                    stepSize: 1,
                                    color: labelColor,
                                    callback: v => ['', '😢', '😔', '😐', '🙂', '😊'][v] ?? v,
                                },
                                grid: { color: gridColor },
                            },
                            x: {
                                ticks: { color: labelColor, maxRotation: 45, autoSkip: true, maxTicksLimit: 12 },
                                grid: { color: gridColor },
                            }
                        },
                        plugins: {
                            legend: { labels: { color: labelColor, font: { size: 12, weight: 'bold' } } },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        if (ctx.datasetIndex === 0) {
                                            const v = ctx.raw;
                                            if (!v) return 'Sem registo';
                                            const labels = { 1: 'Muito difícil', 2: 'Difícil', 3: 'Neutro', 4: 'Bem', 5: 'Muito bem' };
                                            return `Humor: ${v}/5 (${labels[v] || ''})`;
                                        }
                                        return `Média Móvel: ${ctx.raw ? parseFloat(ctx.raw).toFixed(1) : '—'}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
</x-lumina-layout>
