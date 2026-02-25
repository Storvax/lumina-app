{{--
    Formulário do Plano de Segurança Pessoal.
    Estruturado em 6 secções baseadas na metodologia de Stanley & Brown (Safety Planning Intervention).
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
            'icon'        => 'ri-radar-line',
            'color'       => 'amber',
        ],
        'coping_strategies' => [
            'label'       => 'O Que Me Ajuda a Acalmar',
            'description' => 'Estratégias internas que posso usar sozinho(a), sem precisar de ninguém.',
            'placeholder' => 'Ex: Respiração profunda, ouvir música, dar uma caminhada, escrever...',
            'icon'        => 'ri-leaf-line',
            'color'       => 'teal',
        ],
        'reasons_to_live' => [
            'label'       => 'Razões para Continuar',
            'description' => 'As pessoas, momentos e coisas que me dão força.',
            'placeholder' => 'Ex: A minha família, o meu animal de estimação, o mar, a música...',
            'icon'        => 'ri-heart-line',
            'color'       => 'rose',
        ],
        'support_contacts' => [
            'label'       => 'Pessoas de Confiança',
            'description' => 'Alguém que posso contactar quando preciso de apoio.',
            'placeholder' => 'Ex: Nome — Telef. 9XX XXX XXX',
            'icon'        => 'ri-group-line',
            'color'       => 'indigo',
        ],
        'professional_contacts' => [
            'label'       => 'Profissionais de Saúde',
            'description' => 'O meu psicólogo, médico ou linha de apoio de referência.',
            'placeholder' => 'Ex: Dr(a). Nome — Clínica X — Telef. / SNS 24: 808 24 24 24',
            'icon'        => 'ri-stethoscope-line',
            'color'       => 'violet',
        ],
        'environment_safety' => [
            'label'       => 'Tornar o Meu Ambiente Seguro',
            'description' => 'O que posso fazer para reduzir riscos no meu espaço.',
            'placeholder' => 'Ex: Pedir a alguém que guarde medicamentos, evitar estar sozinho(a) à noite...',
            'icon'        => 'ri-shield-check-line',
            'color'       => 'slate',
        ],
    ];
@endphp

<section>
    <header class="mb-6">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="ri-shield-heart-line text-indigo-500"></i>
            O Meu Plano de Segurança
        </h2>
        <p class="mt-1 text-sm text-slate-500 leading-relaxed">
            Este plano é privado e só tu tens acesso. Preenche-o num dia em que te sintas mais estável —
            será o teu guia nos momentos difíceis.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.safety') }}" class="space-y-6">
        @csrf
        @method('POST')

        @foreach($sections as $field => $meta)
            @php
                $colorMap = [
                    'amber'  => ['border' => 'border-amber-200',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'label_text' => 'text-amber-700',  'ring' => 'focus:ring-amber-400'],
                    'teal'   => ['border' => 'border-teal-200',   'icon_bg' => 'bg-teal-100',   'icon_text' => 'text-teal-600',   'label_text' => 'text-teal-700',   'ring' => 'focus:ring-teal-400'],
                    'rose'   => ['border' => 'border-rose-200',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600',   'label_text' => 'text-rose-700',   'ring' => 'focus:ring-rose-400'],
                    'indigo' => ['border' => 'border-indigo-200', 'icon_bg' => 'bg-indigo-100', 'icon_text' => 'text-indigo-600', 'label_text' => 'text-indigo-700', 'ring' => 'focus:ring-indigo-400'],
                    'violet' => ['border' => 'border-violet-200', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600', 'label_text' => 'text-violet-700', 'ring' => 'focus:ring-violet-400'],
                    'slate'  => ['border' => 'border-slate-200',  'icon_bg' => 'bg-slate-100',  'icon_text' => 'text-slate-600',  'label_text' => 'text-slate-700',  'ring' => 'focus:ring-slate-400'],
                ];
                $c = $colorMap[$meta['color']];
            @endphp

            <div class="rounded-2xl border {{ $c['border'] }} bg-white p-5 transition-shadow hover:shadow-sm">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl {{ $c['icon_bg'] }} {{ $c['icon_text'] }} flex items-center justify-center shrink-0 mt-0.5">
                        <i class="{{ $meta['icon'] }} text-lg"></i>
                    </div>
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-bold {{ $c['label_text'] }}">
                            {{ $meta['label'] }}
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $meta['description'] }}</p>
                    </div>
                </div>
                <textarea
                    id="{{ $field }}"
                    name="{{ $field }}"
                    rows="3"
                    maxlength="1000"
                    placeholder="{{ $meta['placeholder'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 placeholder-slate-300 resize-none focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent transition"
                >{{ old($field, $plan[$field] ?? '') }}</textarea>
            </div>
        @endforeach

        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 text-xs text-indigo-700 leading-relaxed">
            <i class="ri-lock-line mr-1"></i>
            <strong>Privacidade total:</strong> Este plano está guardado de forma segura e nunca é partilhado com outros utilizadores.
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('calm.crisis') }}"
               class="text-sm text-slate-500 hover:text-indigo-600 transition-colors flex items-center gap-1">
                <i class="ri-eye-line"></i> Ver o meu plano em crise
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-full transition-colors shadow-sm active:scale-95">
                <i class="ri-save-3-line"></i>
                Guardar Plano
            </button>
        </div>
    </form>

    @if(session('success') === 'safety-plan-updated')
        <div class="mt-4 p-3 bg-teal-50 border border-teal-100 rounded-xl text-sm text-teal-700 flex items-center gap-2">
            <i class="ri-checkbox-circle-line text-teal-500"></i>
            Plano de segurança atualizado. Fica guardado para quando precisares.
        </div>
    @endif
</section>
