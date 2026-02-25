<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modo Crise | Lumina</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Animação suave para a respiração */
        .breathe-circle { animation: breathe 8s ease-in-out infinite; }
        @keyframes breathe {
            0% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.5); opacity: 0.8; }
            100% { transform: scale(1); opacity: 0.3; }
        }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col items-center justify-center p-6 overflow-hidden relative font-sans">

    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-indigo-900/40 to-slate-900 mix-blend-multiply"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/20 rounded-full blur-[100px] breathe-circle pointer-events-none"></div>
    </div>

    <div class="relative z-10 max-w-2xl w-full text-center space-y-12">
        
        <div class="flex flex-col items-center justify-center h-48">
            <div id="breathe-text" class="text-3xl md:text-5xl font-light tracking-widest text-indigo-100 transition-opacity duration-1000">
                Inspira...
            </div>
            <div class="text-indigo-400/50 mt-4 text-sm font-bold tracking-[0.2em] uppercase">Acompanha o círculo</div>
        </div>

        <div class="bg-white/5 border border-white/10 backdrop-blur-xl rounded-[2rem] p-8 md:p-10 shadow-2xl text-left">
            <h2 class="text-sm font-bold text-indigo-300 uppercase tracking-widest mb-6 text-center">
                <i class="ri-shield-heart-line"></i> A Tua Âncora
            </h2>

            @php
                $safetyPlan = [];
                if ($user->safety_plan) {
                    $decoded = json_decode($user->safety_plan, true);
                    $safetyPlan = is_array($decoded) ? $decoded : [];
                }

                $planSections = [
                    'warning_signs'         => ['label' => 'Os teus sinais de alerta',    'icon' => 'ri-radar-line'],
                    'coping_strategies'     => ['label' => 'O que te ajuda a acalmar',    'icon' => 'ri-leaf-line'],
                    'reasons_to_live'       => ['label' => 'As tuas razões para continuar', 'icon' => 'ri-heart-line'],
                    'support_contacts'      => ['label' => 'Pessoas de confiança',         'icon' => 'ri-group-line'],
                    'professional_contacts' => ['label' => 'Profissionais de saúde',       'icon' => 'ri-stethoscope-line'],
                    'environment_safety'    => ['label' => 'O teu ambiente seguro',        'icon' => 'ri-shield-check-line'],
                ];

                // Compatibilidade retroativa: plano guardado como texto simples (formato antigo)
                $isLegacyPlan = $user->safety_plan && empty($safetyPlan) && !is_null($user->safety_plan);
            @endphp

            @if(!empty($safetyPlan))
                <div class="space-y-5">
                    @foreach($planSections as $key => $meta)
                        @if(!empty($safetyPlan[$key]))
                            <div class="border-l-2 border-indigo-400/40 pl-4">
                                <p class="text-xs font-bold text-indigo-300/70 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <i class="{{ $meta['icon'] }}"></i>
                                    {{ $meta['label'] }}
                                </p>
                                <p class="text-slate-100 text-sm leading-relaxed whitespace-pre-line">
                                    {{ $safetyPlan[$key] }}
                                </p>
                            </div>
                        @endif
                    @endforeach
                </div>
                <p class="text-xs text-indigo-300/50 mt-8 text-center">
                    Escreveste isto para ti num momento de clareza. Confia em ti mesmo(a).
                </p>
            @elseif($isLegacyPlan)
                {{-- Compatibilidade com planos guardados antes do formato estruturado --}}
                <p class="text-lg font-medium text-slate-100 leading-relaxed italic whitespace-pre-line text-center">
                    "{{ $user->safety_plan }}"
                </p>
                <p class="text-xs text-indigo-300/60 mt-6 text-center">
                    Este é o plano que escreveste para ti num dia bom.
                </p>
                <div class="mt-4 text-center">
                    <a href="{{ route('profile.edit') }}"
                       class="text-xs text-indigo-400 hover:text-indigo-200 underline transition-colors">
                        Atualizar para o formato estruturado
                    </a>
                </div>
            @else
                <div class="text-center space-y-4">
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Ainda não tens um plano de segurança definido. Num momento mais tranquilo, podes criá-lo
                        no teu perfil — será o teu guia nos momentos difíceis.
                    </p>
                    <p class="text-slate-400 text-sm">Por agora, foca-te na respiração. Estamos aqui contigo.</p>
                    <a href="{{ route('profile.edit') }}"
                       class="inline-block mt-2 text-xs bg-indigo-700/50 hover:bg-indigo-700/80 text-indigo-200 px-4 py-2 rounded-full transition-colors">
                        Criar o meu plano de segurança
                    </a>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-8">
            <a href="tel:112" class="px-8 py-4 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold tracking-wide transition-colors flex items-center gap-2 shadow-lg shadow-rose-900/50">
                <i class="ri-phone-fill"></i> Ligar 112
            </a>
            <a href="{{ route('dashboard') }}" class="px-8 py-4 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold tracking-wide transition-colors border border-white/10 backdrop-blur-md">
                Já me sinto melhor
            </a>
        </div>
    </div>

    <script>
        const textEl = document.getElementById('breathe-text');
        let isInspiring = true;

        setInterval(() => {
            textEl.style.opacity = 0; // fade out
            setTimeout(() => {
                isInspiring = !isInspiring;
                textEl.innerText = isInspiring ? 'Inspira...' : 'Expira...';
                textEl.style.opacity = 1; // fade in
            }, 1000);
        }, 4000);
    </script>
</body>
</html>