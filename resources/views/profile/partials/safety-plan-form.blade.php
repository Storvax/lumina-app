{{--
    Wizard Multi-Step do Plano de Segurança Pessoal.
    Estruturado em 6 passos baseados na metodologia de Stanley & Brown.
    Cada passo é apresentado individualmente com transições suaves,
    barra de progresso e navegação anterior/seguinte.
    Os dados são guardados como JSON na coluna `safety_plan` da tabela `users`.
--}}
@php
    $plan = [];
    if ($user->safety_plan) {
        $decoded = json_decode($user->safety_plan, true);
        $plan = is_array($decoded) ? $decoded : [];
    }

    $sections = [
        'warning_signs' => [
            'label'       => 'Sinais de Alerta',
            'description' => 'O que sinto, penso ou faço quando estou a entrar em crise.',
            'placeholder' => 'Ex: Começo a isolar-me, deixo de comer, fico muito agitado(a)...',
            'hint'        => 'Tenta identificar os primeiros sinais. Reconhecê-los cedo pode fazer toda a diferença.',
            'icon'        => 'ri-radar-line',
            'color'       => 'amber',
        ],
        'coping_strategies' => [
            'label'       => 'O Que Me Ajuda a Acalmar',
            'description' => 'Estratégias internas que posso usar sozinho(a), sem precisar de ninguém.',
            'placeholder' => 'Ex: Respiração profunda, ouvir música, dar uma caminhada, escrever...',
            'hint'        => 'Pensa no que já funcionou antes. Coisas simples contam.',
            'icon'        => 'ri-leaf-line',
            'color'       => 'teal',
        ],
        'reasons_to_live' => [
            'label'       => 'Razões para Continuar',
            'description' => 'As pessoas, momentos e coisas que me dão força.',
            'placeholder' => 'Ex: A minha família, o meu animal de estimação, o mar, a música...',
            'hint'        => 'Não tem de ser grandioso. Pode ser um café quente ou o sorriso de alguém.',
            'icon'        => 'ri-heart-line',
            'color'       => 'rose',
        ],
        'support_contacts' => [
            'label'       => 'Pessoas de Confiança',
            'description' => 'Alguém que posso contactar quando preciso de apoio.',
            'placeholder' => 'Ex: Nome — Telef. 9XX XXX XXX',
            'hint'        => 'Escolhe pelo menos uma pessoa. Não tens de enfrentar tudo sozinho(a).',
            'icon'        => 'ri-group-line',
            'color'       => 'indigo',
        ],
        'professional_contacts' => [
            'label'       => 'Profissionais de Saúde',
            'description' => 'O meu psicólogo, médico ou linha de apoio de referência.',
            'placeholder' => 'Ex: Dr(a). Nome — Clínica X — Telef. / SNS 24: 808 24 24 24',
            'hint'        => 'Se ainda não tens um profissional, a linha SNS 24 (808 24 24 24) está sempre disponível.',
            'icon'        => 'ri-stethoscope-line',
            'color'       => 'violet',
        ],
        'environment_safety' => [
            'label'       => 'Tornar o Meu Ambiente Seguro',
            'description' => 'O que posso fazer para reduzir riscos no meu espaço.',
            'placeholder' => 'Ex: Pedir a alguém que guarde medicamentos, evitar estar sozinho(a) à noite...',
            'hint'        => 'Pequenas mudanças no ambiente podem criar uma rede de segurança importante.',
            'icon'        => 'ri-shield-check-line',
            'color'       => 'slate',
        ],
    ];

    $sectionKeys = array_keys($sections);
    $totalSteps = count($sections);
@endphp

<section x-data="{
    step: 1,
    totalSteps: {{ $totalSteps }},
    saving: false,
    saved: false,
    get progress() { return Math.round((this.step / this.totalSteps) * 100); }
}">
    <header class="mb-6">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="ri-shield-heart-line text-indigo-500"></i>
            O Meu Plano de Segurança
        </h2>
        <p class="mt-1 text-sm text-slate-500 leading-relaxed">
            Vamos construir o teu plano juntos, passo a passo. Preenche-o num dia em que te sintas mais estável — será o teu guia nos momentos difíceis.
        </p>
    </header>

    {{-- Barra de progresso --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-500">
                Passo <span x-text="step"></span> de {{ $totalSteps }}
            </span>
            <span class="text-xs font-bold text-indigo-500" x-text="progress + '%'"></span>
        </div>
        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full transition-all duration-500 ease-out"
                 :style="'width: ' + progress + '%'"></div>
        </div>
        {{-- Step indicators --}}
        <div class="flex justify-between mt-3">
            @foreach($sections as $field => $meta)
                @php $i = array_search($field, $sectionKeys) + 1; @endphp
                <button type="button"
                        @click="step = {{ $i }}"
                        class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-all duration-300"
                        :class="step === {{ $i }}
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 scale-110'
                            : (step > {{ $i }}
                                ? 'bg-indigo-100 text-indigo-600'
                                : 'bg-slate-100 text-slate-400')">
                    <i :class="step > {{ $i }} ? 'ri-check-line' : ''" x-show="step > {{ $i }}"></i>
                    <span x-show="step <= {{ $i }}">{{ $i }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('profile.safety') }}" @submit="saving = true">
        @csrf
        @method('POST')

        @php $stepNum = 0; @endphp
        @foreach($sections as $field => $meta)
            @php
                $stepNum++;
                $colorMap = [
                    'amber'  => ['border' => 'border-amber-200',  'bg' => 'bg-amber-50/50',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'label_text' => 'text-amber-800',  'ring' => 'focus:ring-amber-400',  'hint' => 'text-amber-600/70'],
                    'teal'   => ['border' => 'border-teal-200',   'bg' => 'bg-teal-50/50',   'icon_bg' => 'bg-teal-100',   'icon_text' => 'text-teal-600',   'label_text' => 'text-teal-800',   'ring' => 'focus:ring-teal-400',   'hint' => 'text-teal-600/70'],
                    'rose'   => ['border' => 'border-rose-200',   'bg' => 'bg-rose-50/50',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600',   'label_text' => 'text-rose-800',   'ring' => 'focus:ring-rose-400',   'hint' => 'text-rose-600/70'],
                    'indigo' => ['border' => 'border-indigo-200', 'bg' => 'bg-indigo-50/50', 'icon_bg' => 'bg-indigo-100', 'icon_text' => 'text-indigo-600', 'label_text' => 'text-indigo-800', 'ring' => 'focus:ring-indigo-400', 'hint' => 'text-indigo-600/70'],
                    'violet' => ['border' => 'border-violet-200', 'bg' => 'bg-violet-50/50', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600', 'label_text' => 'text-violet-800', 'ring' => 'focus:ring-violet-400', 'hint' => 'text-violet-600/70'],
                    'slate'  => ['border' => 'border-slate-200',  'bg' => 'bg-slate-50/50',  'icon_bg' => 'bg-slate-100',  'icon_text' => 'text-slate-600',  'label_text' => 'text-slate-800',  'ring' => 'focus:ring-slate-400',  'hint' => 'text-slate-500/70'],
                ];
                $c = $colorMap[$meta['color']];
            @endphp

            <div x-show="step === {{ $stepNum }}"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 class="rounded-2xl border {{ $c['border'] }} {{ $c['bg'] }} p-6 sm:p-8">

                <div class="flex items-start gap-4 mb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $c['icon_bg'] }} {{ $c['icon_text'] }} flex items-center justify-center shrink-0">
                        <i class="{{ $meta['icon'] }} text-2xl"></i>
                    </div>
                    <div>
                        <label for="{{ $field }}" class="block text-base font-bold {{ $c['label_text'] }}">
                            {{ $meta['label'] }}
                        </label>
                        <p class="text-sm text-slate-500 mt-1">{{ $meta['description'] }}</p>
                    </div>
                </div>

                <textarea
                    id="{{ $field }}"
                    name="{{ $field }}"
                    rows="5"
                    maxlength="1000"
                    placeholder="{{ $meta['placeholder'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 placeholder-slate-300 resize-none focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent transition"
                >{{ old($field, $plan[$field] ?? '') }}</textarea>

                <p class="text-xs {{ $c['hint'] }} mt-3 flex items-center gap-1.5">
                    <i class="ri-lightbulb-line"></i>
                    {{ $meta['hint'] }}
                </p>
            </div>
        @endforeach

        {{-- Navegação do wizard --}}
        <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
            <button type="button"
                    x-show="step > 1"
                    @click="step--"
                    class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-all">
                <i class="ri-arrow-left-line"></i>
                Anterior
            </button>
            <span x-show="step === 1"></span>

            <div class="flex items-center gap-3">
                <a href="{{ route('calm.crisis') }}"
                   class="text-xs text-slate-400 hover:text-indigo-600 transition-colors flex items-center gap-1">
                    <i class="ri-eye-line"></i> Ver plano
                </a>

                <button type="button"
                        x-show="step < totalSteps"
                        @click="step++"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-full transition-colors shadow-sm active:scale-95">
                    Seguinte
                    <i class="ri-arrow-right-line"></i>
                </button>

                <button type="submit"
                        x-show="step === totalSteps"
                        :disabled="saving"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-2.5 rounded-full transition-colors shadow-sm active:scale-95 disabled:opacity-50">
                    <i class="ri-save-3-line" x-show="!saving"></i>
                    <i class="ri-loader-4-line animate-spin" x-show="saving"></i>
                    <span x-text="saving ? 'A guardar...' : 'Guardar Plano'"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Aviso de privacidade --}}
    <div class="mt-6 bg-indigo-50 border border-indigo-100 rounded-2xl p-4 text-xs text-indigo-700 leading-relaxed">
        <i class="ri-lock-line mr-1"></i>
        <strong>Privacidade total:</strong> Este plano está guardado de forma segura e nunca é partilhado com outros utilizadores.
    </div>

    @if(session('success') === 'safety-plan-updated')
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mt-4 p-3 bg-teal-50 border border-teal-100 rounded-xl text-sm text-teal-700 flex items-center gap-2">
            <i class="ri-checkbox-circle-line text-teal-500"></i>
            Plano de segurança atualizado. Fica guardado para quando precisares.
        </div>
    @endif
</section>
