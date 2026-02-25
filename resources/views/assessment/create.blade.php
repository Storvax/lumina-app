<x-lumina-layout title="{{ $type === 'phq9' ? 'PHQ-9' : 'GAD-7' }} | Lumina">
    @php
        $isPHQ = $type === 'phq9';
        $accentColor = $isPHQ ? 'violet' : 'teal';
        $title = $isPHQ ? 'Questionário PHQ-9' : 'Questionário GAD-7';
        $subtitle = $isPHQ ? 'Rastreio de Depressão' : 'Rastreio de Ansiedade';
        $instruction = 'Nas últimas 2 semanas, com que frequência foste incomodado(a) por algum dos seguintes problemas?';
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6"
         x-data="{
             answers: Array({{ count($questions) }}).fill(null),
             currentQ: 0,
             submitted: false,
             get progress() { return this.answers.filter(a => a !== null).length; },
             get allAnswered() { return this.progress === {{ count($questions) }}; },
             selectAnswer(index, value) {
                 this.answers[index] = value;
                 if (index < {{ count($questions) - 1 }}) {
                     setTimeout(() => { this.currentQ = index + 1; }, 300);
                 }
             }
         }">

        {{-- Cabeçalho --}}
        <div class="text-center mb-6">
            <a href="{{ route('assessment.index') }}" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 font-bold mb-4 transition-colors">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="text-xs text-{{ $accentColor }}-600 font-bold mt-1">{{ $subtitle }}</p>
            <p class="text-sm text-slate-500 mt-3">{{ $instruction }}</p>
        </div>

        {{-- Barra de progresso --}}
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold text-slate-400" x-text="progress + ' de {{ count($questions) }}'"></span>
                <span class="text-[10px] font-bold text-slate-400" x-text="Math.round((progress / {{ count($questions) }}) * 100) + '%'"></span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-{{ $accentColor }}-500 rounded-full transition-all duration-500"
                     :style="'width: ' + ((progress / {{ count($questions) }}) * 100) + '%'"></div>
            </div>
        </div>

        {{-- Formulário --}}
        <form method="POST" action="{{ route('assessment.store', $type) }}" @submit="submitted = true">
            @csrf

            <div class="space-y-4">
                @foreach($questions as $index => $question)
                    <div class="bg-white rounded-2xl border-2 transition-all duration-300 overflow-hidden"
                         :class="currentQ === {{ $index }}
                             ? 'border-{{ $accentColor }}-300 shadow-md'
                             : answers[{{ $index }}] !== null
                                 ? 'border-{{ $accentColor }}-100 opacity-75'
                                 : 'border-slate-100'"
                         @click="currentQ = {{ $index }}">

                        <div class="p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <span class="shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold transition-colors"
                                      :class="answers[{{ $index }}] !== null ? 'bg-{{ $accentColor }}-100 text-{{ $accentColor }}-700' : 'bg-slate-100 text-slate-400'">
                                    {{ $index + 1 }}
                                </span>
                                <p class="text-sm font-bold text-slate-800 leading-snug pt-0.5">{{ $question }}</p>
                            </div>

                            {{-- Pergunta 9 do PHQ-9 é sobre ideação suicida — tratamento especial --}}
                            @if($isPHQ && $index === 8)
                                <div class="bg-rose-50 border border-rose-100 rounded-xl p-3 mb-3">
                                    <p class="text-[10px] text-rose-700 font-bold flex items-center gap-1">
                                        <i class="ri-alert-line"></i>
                                        Se tens estes pensamentos, não estás sozinho(a).
                                        <a href="tel:808242424" class="underline">SNS 24: 808 24 24 24</a>
                                    </p>
                                </div>
                            @endif

                            <div x-show="currentQ === {{ $index }}" x-collapse class="grid grid-cols-2 gap-2">
                                @foreach($options as $value => $label)
                                    <button type="button"
                                            @click="selectAnswer({{ $index }}, {{ $value }})"
                                            class="py-3 px-3 rounded-xl border-2 text-xs font-bold text-center transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-{{ $accentColor }}-500"
                                            :class="answers[{{ $index }}] === {{ $value }}
                                                ? 'border-{{ $accentColor }}-500 bg-{{ $accentColor }}-50 text-{{ $accentColor }}-700'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-{{ $accentColor }}-200 hover:bg-{{ $accentColor }}-50/30'">
                                        <span class="block text-lg mb-1">{{ $value }}</span>
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>

                            {{-- Resposta selecionada (quando colapsado) --}}
                            <div x-show="currentQ !== {{ $index }} && answers[{{ $index }}] !== null" class="text-xs text-{{ $accentColor }}-600 font-bold">
                                <i class="ri-check-line"></i>
                                <span x-text="[{{ collect($options)->map(fn($l) => "'$l'")->implode(',') }}][answers[{{ $index }}]]"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden inputs --}}
                    <input type="hidden" name="answers[{{ $index }}]" :value="answers[{{ $index }}]">
                @endforeach
            </div>

            {{-- Botão submeter --}}
            <div class="mt-8 mb-8">
                <button type="submit"
                        :disabled="!allAnswered || submitted"
                        class="w-full py-4 rounded-2xl font-bold text-white text-base transition-all duration-300 bg-{{ $accentColor }}-600 hover:bg-{{ $accentColor }}-700 active:scale-[0.98] disabled:opacity-30 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-{{ $accentColor }}-500">
                    <span x-show="!submitted">Ver resultado</span>
                    <span x-show="submitted" class="flex items-center justify-center gap-2">
                        <i class="ri-loader-4-line animate-spin"></i> A processar...
                    </span>
                </button>
                <p class="text-[10px] text-slate-400 text-center mt-3">
                    Os teus dados são privados e encriptados. Apenas tu podes ver os teus resultados.
                </p>
            </div>
        </form>
    </div>
</x-lumina-layout>
