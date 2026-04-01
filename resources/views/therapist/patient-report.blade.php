<x-lumina-layout title="Relatório de Progresso | Lumina PRO">
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

            <div class="mb-8">
                <a href="{{ route('therapist.dashboard') }}" class="text-sm text-slate-400 hover:text-indigo-500 flex items-center gap-1 mb-3 transition-colors">
                    <i class="ri-arrow-left-s-line"></i> Voltar ao Dashboard
                </a>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="ri-bar-chart-2-line text-indigo-500"></i> Relatório de Progresso
                    </h1>
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $patient->pseudonym }} — últimos 30 dias
                    </span>
                </div>
            </div>

            {{-- Alerta de crise --}}
            @if($report['crisis_alerts']['count'] > 0)
                <div class="mb-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 bg-rose-100 text-rose-500 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                        <i class="ri-alarm-warning-line"></i>
                    </div>
                    <div>
                        <p class="font-bold text-rose-700 dark:text-rose-300 text-sm">
                            {{ $report['crisis_alerts']['count'] }} {{ $report['crisis_alerts']['count'] === 1 ? 'dia' : 'dias' }} com humor muito baixo (≤2) nos últimos 30 dias.
                        </p>
                        <p class="text-rose-500 text-xs mt-1">
                            Datas: {{ implode(', ', $report['crisis_alerts']['dates']) }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Métricas principais --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                    <p class="text-2xl font-black text-slate-800 dark:text-white">
                        {{ $report['mood_summary']['avg'] ? number_format($report['mood_summary']['avg'], 1) : '—' }}<span class="text-sm text-slate-400">/5</span>
                    </p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Humor Médio</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                    <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $report['frequency']['filled_days'] }}<span class="text-sm text-slate-400">/{{ $report['frequency']['total_days'] }}</span></p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Dias Registados</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                    <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $report['engagement'] }}<span class="text-sm text-slate-400">%</span></p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Envolvimento</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 shadow-sm text-center">
                    @php
                        $trend = $report['mood_summary']['trend'];
                        [$icon, $color, $label] = match($trend) {
                            'improving' => ['ri-arrow-up-line', 'text-teal-500', 'A Melhorar'],
                            'declining' => ['ri-arrow-down-line', 'text-rose-400', 'Em Queda'],
                            'stable'    => ['ri-subtract-line', 'text-amber-400', 'Estável'],
                            default     => ['ri-question-line', 'text-slate-400', 'Sem dados'],
                        };
                    @endphp
                    <p class="text-2xl font-black {{ $color }}"><i class="{{ $icon }}"></i></p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $label }}</p>
                </div>
            </div>

            {{-- Gráfico de humor --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm mb-6">
                <h2 class="font-bold text-slate-700 dark:text-white mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <i class="ri-line-chart-line text-indigo-500"></i> Evolução do Humor (30 dias)
                </h2>
                <canvas id="moodChart" height="80"></canvas>
            </div>

            {{-- Tags mais frequentes --}}
            @if(count($report['tag_frequency']) > 0)
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm mb-6">
                    <h2 class="font-bold text-slate-700 dark:text-white mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="ri-price-tag-3-line text-indigo-500"></i> Emoções Mais Frequentes
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($report['tag_frequency'] as $tag => $count)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-bold">
                                {{ $tag }} <span class="bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300 text-xs px-1.5 py-0.5 rounded-full">{{ $count }}×</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Ações --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('clinical-notes.index', $patient) }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                    <i class="ri-file-lock-line"></i> Ver Notas Clínicas
                </a>
                <a href="{{ route('therapist.dashboard') }}"
                   class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                    <i class="ri-arrow-left-line"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const labelColor = isDark ? '#94a3b8' : '#64748b';

            new Chart(document.getElementById('moodChart'), {
                type: 'line',
                data: {
                    labels: @json($report['mood_series']['labels']),
                    datasets: [{
                        label: 'Humor',
                        data: @json($report['mood_series']['values']),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 4,
                        tension: 0.4,
                        fill: true,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { min: 1, max: 5, ticks: { stepSize: 1, color: labelColor }, grid: { color: gridColor } },
                        x: { ticks: { color: labelColor, autoSkip: true, maxTicksLimit: 10 }, grid: { color: gridColor } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const v = ctx.raw;
                                    if (!v) return 'Sem registo';
                                    const labels = { 1: 'Muito difícil', 2: 'Difícil', 3: 'Neutro', 4: 'Bem', 5: 'Muito bem' };
                                    return `Humor: ${v}/5 (${labels[v] || ''})`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-lumina-layout>
