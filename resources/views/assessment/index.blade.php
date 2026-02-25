<x-lumina-layout title="Auto-avaliação | Lumina">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        {{-- Cabeçalho --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-600 mb-4">
                <i class="ri-heart-pulse-line text-3xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Auto-avaliação</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
                Questionários clínicos validados, privados e opcionais. Os teus dados nunca são partilhados.
            </p>
        </div>

        {{-- Aviso clínico --}}
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-8">
            <div class="flex items-start gap-3">
                <i class="ri-alert-line text-amber-600 text-xl shrink-0 mt-0.5"></i>
                <div class="text-xs text-amber-800 leading-relaxed">
                    <p class="font-bold mb-1">Estes questionários não substituem diagnóstico profissional.</p>
                    <p>São ferramentas de rastreio usadas mundialmente para auto-conhecimento. Se os teus resultados te preocupam, fala com um profissional de saúde.</p>
                </div>
            </div>
        </div>

        {{-- Cards de questionário --}}
        <div class="grid sm:grid-cols-2 gap-4 mb-12">
            {{-- PHQ-9 --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                        <i class="ri-mental-health-line text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">PHQ-9</h2>
                        <p class="text-xs text-slate-500">Rastreio de depressão</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-4">9 perguntas · ~2 minutos · Questionário de Saúde do Paciente</p>
                <a href="{{ route('assessment.create', 'phq9') }}"
                   class="block w-full text-center py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold transition-colors">
                    Iniciar avaliação
                </a>
                @if($phq9History->isNotEmpty())
                    <p class="text-[10px] text-slate-400 text-center mt-2">
                        Último: {{ $phq9History->first()->created_at->diffForHumans() }}
                        — {{ \App\Models\SelfAssessment::severityLabel($phq9History->first()->severity) }}
                    </p>
                @endif
            </div>

            {{-- GAD-7 --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center">
                        <i class="ri-pulse-line text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">GAD-7</h2>
                        <p class="text-xs text-slate-500">Rastreio de ansiedade</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-4">7 perguntas · ~2 minutos · Escala de Ansiedade Generalizada</p>
                <a href="{{ route('assessment.create', 'gad7') }}"
                   class="block w-full text-center py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold transition-colors">
                    Iniciar avaliação
                </a>
                @if($gad7History->isNotEmpty())
                    <p class="text-[10px] text-slate-400 text-center mt-2">
                        Último: {{ $gad7History->first()->created_at->diffForHumans() }}
                        — {{ \App\Models\SelfAssessment::severityLabel($gad7History->first()->severity) }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Gráfico de evolução --}}
        @if($assessments->count() >= 2)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="ri-line-chart-line text-indigo-500"></i>
                    Evolução ao longo do tempo
                </h3>

                <div class="space-y-6">
                    @foreach(['phq9' => ['label' => 'Depressão (PHQ-9)', 'color' => 'violet', 'max' => 27], 'gad7' => ['label' => 'Ansiedade (GAD-7)', 'color' => 'teal', 'max' => 21]] as $chartType => $chartMeta)
                        @php $history = ${$chartType . 'History'}; @endphp
                        @if($history->count() >= 2)
                            <div>
                                <p class="text-xs font-bold text-slate-600 mb-3">{{ $chartMeta['label'] }}</p>
                                <div class="flex items-end gap-1 h-24">
                                    @foreach($history->sortBy('created_at')->take(12) as $entry)
                                        @php
                                            $pct = ($entry->total_score / $chartMeta['max']) * 100;
                                            $color = \App\Models\SelfAssessment::severityColor($entry->severity);
                                        @endphp
                                        <a href="{{ route('assessment.result', $entry) }}"
                                           class="flex-1 rounded-t-lg bg-{{ $color }}-200 hover:bg-{{ $color }}-300 transition-colors relative group"
                                           style="height: {{ max($pct, 8) }}%"
                                           title="{{ $entry->created_at->format('d/m') }} — {{ $entry->total_score }}pts">
                                            <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] font-bold text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                {{ $entry->total_score }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span class="text-[9px] text-slate-400">{{ $history->sortBy('created_at')->first()->created_at->format('d/m') }}</span>
                                    <span class="text-[9px] text-slate-400">{{ $history->sortBy('created_at')->last()->created_at->format('d/m') }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Histórico completo --}}
        @if($assessments->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <i class="ri-history-line text-slate-400"></i>
                        Histórico
                    </h3>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($assessments->take(20) as $entry)
                        @php $color = \App\Models\SelfAssessment::severityColor($entry->severity); @endphp
                        <a href="{{ route('assessment.result', $entry) }}"
                           class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold uppercase text-{{ $entry->type === 'phq9' ? 'violet' : 'teal' }}-600 bg-{{ $entry->type === 'phq9' ? 'violet' : 'teal' }}-50 px-2 py-1 rounded-lg">
                                    {{ strtoupper($entry->type) }}
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ $entry->total_score }} pontos</p>
                                    <p class="text-[10px] text-slate-400">{{ $entry->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-{{ $color }}-600 bg-{{ $color }}-50 px-3 py-1 rounded-full">
                                {{ \App\Models\SelfAssessment::severityLabel($entry->severity) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-lumina-layout>
