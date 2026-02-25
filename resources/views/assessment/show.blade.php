<x-lumina-layout title="Resultado | Lumina">
    @php
        $isPHQ = $assessment->type === 'phq9';
        $accentColor = $isPHQ ? 'violet' : 'teal';
        $title = $isPHQ ? 'PHQ-9 — Depressão' : 'GAD-7 — Ansiedade';
        $maxScore = $isPHQ ? 27 : 21;
        $severityLabel = \App\Models\SelfAssessment::severityLabel($assessment->severity);
        $severityColor = \App\Models\SelfAssessment::severityColor($assessment->severity);
        $pct = round(($assessment->total_score / $maxScore) * 100);
        $options = \App\Models\SelfAssessment::answerOptions();
        $answers = $assessment->answers;
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-2">
            <a href="{{ route('assessment.index') }}" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 font-bold mb-4 transition-colors">
                <i class="ri-arrow-left-line"></i> Voltar ao histórico
            </a>
        </div>

        {{-- Card principal do resultado --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-br from-{{ $accentColor }}-50 to-white p-8 text-center">
                <p class="text-xs font-bold text-{{ $accentColor }}-600 uppercase tracking-wider mb-2">{{ $title }}</p>
                <div class="relative inline-flex items-center justify-center w-32 h-32 mb-4">
                    {{-- Anel de progresso via SVG --}}
                    <svg class="w-32 h-32 -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                        <circle cx="60" cy="60" r="52" fill="none"
                                stroke="currentColor"
                                stroke-width="8"
                                stroke-linecap="round"
                                stroke-dasharray="{{ 2 * 3.14159 * 52 }}"
                                stroke-dashoffset="{{ (2 * 3.14159 * 52) * (1 - $pct / 100) }}"
                                class="text-{{ $severityColor }}-500 transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-slate-900">{{ $assessment->total_score }}</span>
                        <span class="text-[10px] text-slate-400 font-bold">de {{ $maxScore }}</span>
                    </div>
                </div>
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-bold bg-{{ $severityColor }}-100 text-{{ $severityColor }}-700">
                    {{ $severityLabel }}
                </span>
                <p class="text-[10px] text-slate-400 mt-3">{{ $assessment->created_at->format('d M Y, H:i') }}</p>
            </div>

            {{-- Comparação com avaliação anterior --}}
            @if($previousAssessment)
                @php
                    $diff = $assessment->total_score - $previousAssessment->total_score;
                    $improved = $diff < 0;
                @endphp
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-center gap-2">
                    @if($diff === 0)
                        <i class="ri-arrow-right-line text-slate-400"></i>
                        <span class="text-xs font-bold text-slate-500">Sem alteração desde a última avaliação</span>
                    @elseif($improved)
                        <i class="ri-arrow-down-line text-teal-500"></i>
                        <span class="text-xs font-bold text-teal-600">{{ abs($diff) }} pontos a menos — evolução positiva</span>
                    @else
                        <i class="ri-arrow-up-line text-amber-500"></i>
                        <span class="text-xs font-bold text-amber-600">{{ $diff }} pontos a mais — atenção redobrada</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Interpretação e recomendações --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
            <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i class="ri-information-line text-{{ $accentColor }}-500"></i>
                O que significa este resultado?
            </h3>

            @if($isPHQ)
                @switch($assessment->severity)
                    @case('minimal')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere sintomas mínimos de depressão. Continua a cuidar de ti e a monitorizar o teu bem-estar.</p>
                        @break
                    @case('mild')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere sintomas ligeiros. Práticas de autocuidado e a monitorização regular podem ajudar. Considera falar com alguém de confiança.</p>
                        @break
                    @case('moderate')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere sintomas moderados. Recomendamos que consultes um profissional de saúde para uma avaliação mais completa.</p>
                        @break
                    @case('moderately_severe')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere sintomas moderadamente graves. É importante que fales com um profissional de saúde mental sobre o que estás a sentir.</p>
                        @break
                    @case('severe')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere sintomas graves. Recomendamos fortemente que procures apoio profissional. Não estás sozinho(a) — há ajuda disponível.</p>
                        @break
                @endswitch
            @else
                @switch($assessment->severity)
                    @case('minimal')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere ansiedade mínima. Continua a praticar técnicas de relaxamento como parte do teu dia-a-dia.</p>
                        @break
                    @case('mild')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere ansiedade ligeira. Técnicas de respiração, exercício e mindfulness podem ajudar a manter o equilíbrio.</p>
                        @break
                    @case('moderate')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere ansiedade moderada. Recomendamos que consultes um profissional para avaliar se precisas de apoio adicional.</p>
                        @break
                    @case('severe')
                        <p class="text-sm text-slate-600 leading-relaxed">O teu resultado sugere ansiedade grave. É importante que procures apoio profissional. A ansiedade é tratável e não precisas de enfrentar isto sozinho(a).</p>
                        @break
                @endswitch
            @endif
        </div>

        {{-- Recursos sugeridos --}}
        @if(in_array($assessment->severity, ['moderate', 'moderately_severe', 'severe']))
            <div class="bg-{{ $severityColor }}-50 border border-{{ $severityColor }}-200 rounded-2xl p-6 mb-6">
                <h3 class="font-bold text-{{ $severityColor }}-900 mb-3 flex items-center gap-2">
                    <i class="ri-shield-heart-line"></i>
                    Recursos disponíveis
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('calm.crisis') }}" class="flex items-center gap-3 bg-white rounded-xl p-3 hover:shadow-sm transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i class="ri-shield-star-line text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Zona de Crise</p>
                            <p class="text-[10px] text-slate-500">Recursos imediatos e plano de segurança</p>
                        </div>
                    </a>
                    <a href="{{ route('calm.grounding') }}" class="flex items-center gap-3 bg-white rounded-xl p-3 hover:shadow-sm transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                            <i class="ri-leaf-line text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Grounding 5-4-3-2-1</p>
                            <p class="text-[10px] text-slate-500">Técnica de ancoragem ao presente</p>
                        </div>
                    </a>
                    <a href="tel:808242424" class="flex items-center gap-3 bg-white rounded-xl p-3 hover:shadow-sm transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="ri-phone-line text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">SNS 24 — 808 24 24 24</p>
                            <p class="text-[10px] text-slate-500">Apoio psicológico disponível 24h</p>
                        </div>
                    </a>
                </div>
            </div>
        @endif

        {{-- Detalhes das respostas --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8" x-data="{ showDetails: false }">
            <button @click="showDetails = !showDetails"
                    class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <i class="ri-list-check text-slate-400"></i>
                    Ver respostas detalhadas
                </span>
                <i class="ri-arrow-down-s-line text-slate-400 transition-transform" :class="showDetails && 'rotate-180'"></i>
            </button>
            <div x-show="showDetails" x-collapse class="border-t border-slate-100">
                @foreach($questions as $index => $question)
                    <div class="px-6 py-3 flex items-start gap-3 {{ $index % 2 === 0 ? 'bg-slate-50/50' : '' }}">
                        <span class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $index + 1 }}.</span>
                        <div class="flex-1">
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $question }}</p>
                            <p class="text-xs font-bold text-{{ $accentColor }}-600 mt-1">
                                {{ $answers[$index] ?? 0 }} — {{ $options[$answers[$index] ?? 0] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ações --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-8">
            <a href="{{ route('assessment.create', $assessment->type) }}"
               class="flex-1 text-center py-3 rounded-xl bg-{{ $accentColor }}-600 hover:bg-{{ $accentColor }}-700 text-white font-bold text-sm transition-colors">
                Repetir avaliação
            </a>
            <a href="{{ route('assessment.index') }}"
               class="flex-1 text-center py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition-colors">
                Ver histórico completo
            </a>
        </div>

        {{-- Aviso --}}
        <p class="text-[10px] text-slate-400 text-center pb-4 leading-relaxed">
            Este questionário é uma ferramenta de rastreio e não substitui uma avaliação clínica profissional.
            Em caso de emergência, liga o <strong>112</strong> ou o <strong>SNS 24 (808 24 24 24)</strong>.
        </p>
    </div>
</x-lumina-layout>
