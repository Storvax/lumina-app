<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bem-vindo(a) | Lumina</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .step-enter { animation: stepIn 0.5s ease-out forwards; }
        @keyframes stepIn {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-600 min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">

    {{-- Fundo decorativo --}}
    <div class="fixed inset-0 bg-gradient-to-b from-indigo-100/40 via-white to-teal-50/20 -z-10"></div>
    <div class="fixed top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-300/10 rounded-full blur-[120px] -z-10"></div>

    <div class="w-full max-w-lg" x-data="onboardingWizard()">

        {{-- Logo --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-400 flex items-center justify-center text-white font-bold text-xl">L</div>
                <span class="text-2xl font-bold text-slate-800 tracking-tight">Lumina<span class="text-indigo-500">.</span></span>
            </div>
        </div>

        {{-- Progresso --}}
        <div class="flex items-center justify-center gap-2 mb-8">
            <template x-for="i in 3" :key="i">
                <div class="h-1.5 rounded-full transition-all duration-500"
                     :class="step >= i ? 'w-12 bg-indigo-500' : 'w-6 bg-slate-200'">
                </div>
            </template>
        </div>

        <form method="POST" action="{{ route('onboarding.store') }}" @submit="submitting = true">
            @csrf

            {{-- Passo 1: Intenção --}}
            <div x-show="step === 1" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">O que te trouxe aqui?</h1>
                    <p class="text-sm text-slate-500">Não há respostas erradas. Isto ajuda-nos a personalizar a tua experiência.</p>
                </div>

                <div class="space-y-3">
                    @php
                        $intents = [
                            'crisis'  => ['icon' => 'ri-alarm-warning-line', 'label' => 'Estou em crise ou a passar mal',   'color' => 'rose'],
                            'talk'    => ['icon' => 'ri-chat-voice-line',    'label' => 'Quero falar com alguém',            'color' => 'orange'],
                            'write'   => ['icon' => 'ri-quill-pen-line',     'label' => 'Quero escrever sobre o que sinto',  'color' => 'indigo'],
                            'learn'   => ['icon' => 'ri-book-open-line',     'label' => 'Quero aprender sobre saúde mental', 'color' => 'teal'],
                            'explore' => ['icon' => 'ri-compass-3-line',     'label' => 'Estou só a explorar',               'color' => 'slate'],
                        ];
                    @endphp

                    @foreach($intents as $value => $meta)
                        <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="intent === '{{ $value }}'
                                   ? 'border-{{ $meta['color'] }}-400 bg-{{ $meta['color'] }}-50 shadow-sm'
                                   : 'border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="intent" value="{{ $value }}"
                                   x-model="intent" class="sr-only">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                 :class="intent === '{{ $value }}' ? 'bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-600' : 'bg-slate-100 text-slate-400'">
                                <i class="{{ $meta['icon'] }} text-xl"></i>
                            </div>
                            <span class="text-sm font-bold"
                                  :class="intent === '{{ $value }}' ? 'text-slate-900' : 'text-slate-600'">
                                {{ $meta['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <button type="button"
                        @click="if(intent) step = 2"
                        :disabled="!intent"
                        class="w-full mt-8 py-3.5 rounded-2xl font-bold text-white transition-all disabled:opacity-30 disabled:cursor-not-allowed bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98]">
                    Continuar
                </button>
            </div>

            {{-- Passo 2: Estado emocional --}}
            <div x-show="step === 2" x-transition class="step-enter" style="display: none;">
                <div class="text-center mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">Como te sentes agora?</h1>
                    <p class="text-sm text-slate-500">Sem julgamentos. Sê honesto(a) contigo mesmo(a).</p>
                </div>

                <div class="flex justify-center gap-4 mb-8">
                    @php
                        $moods = [
                            1 => ['emoji' => '😔', 'label' => 'Muito em baixo'],
                            2 => ['emoji' => '😞', 'label' => 'Em baixo'],
                            3 => ['emoji' => '😐', 'label' => 'Neutro'],
                            4 => ['emoji' => '🙂', 'label' => 'Bem'],
                            5 => ['emoji' => '😊', 'label' => 'Muito bem'],
                        ];
                    @endphp

                    @foreach($moods as $value => $meta)
                        <label class="flex flex-col items-center gap-2 cursor-pointer group">
                            <input type="radio" name="mood" value="{{ $value }}"
                                   x-model="mood" class="sr-only">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl border-2 transition-all"
                                 :class="mood == '{{ $value }}'
                                     ? 'border-indigo-400 bg-indigo-50 shadow-sm scale-110'
                                     : 'border-slate-100 bg-white group-hover:border-slate-200'">
                                {{ $meta['emoji'] }}
                            </div>
                            <span class="text-[10px] font-bold transition-colors"
                                  :class="mood == '{{ $value }}' ? 'text-indigo-600' : 'text-slate-400'">
                                {{ $meta['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="step = 1"
                            class="px-6 py-3.5 rounded-2xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <button type="button"
                            @click="if(mood) step = 3"
                            :disabled="!mood"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-white transition-all disabled:opacity-30 disabled:cursor-not-allowed bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98]">
                        Continuar
                    </button>
                </div>
            </div>

            {{-- Passo 3: Preferência --}}
            <div x-show="step === 3" x-transition class="step-enter" style="display: none;">
                <div class="text-center mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">Como preferes expressar-te?</h1>
                    <p class="text-sm text-slate-500">Cada pessoa tem o seu ritmo. Qual é o teu?</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @php
                        $prefs = [
                            'read'   => ['icon' => 'ri-book-open-line',  'label' => 'Ler e refletir',    'desc' => 'Artigos, histórias de outros'],
                            'listen' => ['icon' => 'ri-headphone-line',  'label' => 'Ouvir e absorver',  'desc' => 'Música, áudios, silêncio'],
                            'talk'   => ['icon' => 'ri-chat-voice-line', 'label' => 'Falar e partilhar', 'desc' => 'Chat, fórum, conversa'],
                            'create' => ['icon' => 'ri-pencil-line',     'label' => 'Escrever e criar',  'desc' => 'Diário, desabafos, arte'],
                        ];
                    @endphp

                    @foreach($prefs as $value => $meta)
                        <label class="flex flex-col items-center text-center p-5 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="preference === '{{ $value }}'
                                   ? 'border-indigo-400 bg-indigo-50 shadow-sm'
                                   : 'border-slate-100 bg-white hover:border-slate-200'">
                            <input type="radio" name="preference" value="{{ $value }}"
                                   x-model="preference" class="sr-only">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 transition-colors"
                                 :class="preference === '{{ $value }}' ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400'">
                                <i class="{{ $meta['icon'] }} text-2xl"></i>
                            </div>
                            <span class="text-sm font-bold"
                                  :class="preference === '{{ $value }}' ? 'text-slate-900' : 'text-slate-700'">
                                {{ $meta['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1">{{ $meta['desc'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex gap-3 mt-8">
                    <button type="button" @click="step = 2"
                            class="px-6 py-3.5 rounded-2xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <button type="submit"
                            :disabled="!preference || submitting"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-white transition-all disabled:opacity-30 disabled:cursor-not-allowed bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98]">
                        <span x-show="!submitting">Começar a minha jornada</span>
                        <span x-show="submitting" class="flex items-center justify-center gap-2">
                            <i class="ri-loader-4-line animate-spin"></i> A preparar...
                        </span>
                    </button>
                </div>
            </div>
        </form>

        {{-- Link de emergência sempre visível --}}
        <div class="text-center mt-8">
            <p class="text-xs text-slate-400">
                Em emergência? <a href="tel:112" class="text-rose-500 font-bold hover:underline">Liga 112</a>
                ou <a href="tel:808242424" class="text-blue-500 font-bold hover:underline">SNS 24</a>
            </p>
        </div>
    </div>

    <script>
        function onboardingWizard() {
            return {
                step: 1,
                intent: null,
                mood: null,
                preference: null,
                submitting: false,
            };
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
