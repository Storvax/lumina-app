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
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-700 min-h-screen flex flex-col items-center py-6 sm:py-10 sm:justify-center px-4 sm:px-6 relative overflow-x-hidden">

    {{-- Fundo decorativo --}}
    <div class="fixed inset-0 bg-gradient-to-br from-indigo-50 via-white to-teal-50/40 -z-10 pointer-events-none"></div>
    <div class="fixed top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-200/20 rounded-full blur-[100px] -z-10 pointer-events-none"></div>

    <div class="w-full max-w-lg"
         x-data="{
             step: 1,
             intent: null,
             mood: null,
             preference: null,
             submitting: false,
             next(requiredField) {
                 if (this[requiredField]) this.step++;
             }
         }">

        {{-- Cabeçalho / Logo --}}
        <div class="text-center mb-4 sm:mb-8">
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-400 flex items-center justify-center text-white font-bold text-xl shadow-md">L</div>
                <span class="text-2xl font-bold text-slate-800 tracking-tight">Lumina<span class="text-indigo-500">.</span></span>
            </div>
            <p class="text-sm text-slate-400">O teu espaço seguro</p>
        </div>

        {{-- Barra de progresso --}}
        <div class="flex items-center justify-center gap-2 mb-4 sm:mb-8">
            <template x-for="i in 3" :key="i">
                <div class="h-1.5 rounded-full transition-all duration-500"
                     :class="step >= i ? 'w-14 bg-indigo-500' : 'w-6 bg-slate-200'"></div>
            </template>
        </div>

        {{-- Campos ocultos para submissão — preenchidos pelo estado Alpine --}}
        <form method="POST" action="{{ route('onboarding.store') }}" x-ref="form" @submit="submitting = true">
            @csrf
            <input type="hidden" name="intent"     :value="intent">
            <input type="hidden" name="mood"       :value="mood">
            <input type="hidden" name="preference" :value="preference">

            {{-- ═══════════════════════════════
                 PASSO 1 — Intenção
            ═══════════════════════════════ --}}
            <div x-show="step === 1"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0">

                <div class="text-center mb-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">O que te trouxe aqui?</h1>
                    <p class="text-sm text-slate-500">Não há respostas erradas.</p>
                </div>

                <div class="space-y-2 sm:space-y-3">
                    @php
                        $intents = [
                            ['value' => 'crisis',  'icon' => 'ri-alarm-warning-line', 'label' => 'Estou em crise ou a passar mal'],
                            ['value' => 'talk',    'icon' => 'ri-chat-voice-line',    'label' => 'Quero falar com alguém'],
                            ['value' => 'write',   'icon' => 'ri-quill-pen-line',     'label' => 'Quero escrever sobre o que sinto'],
                            ['value' => 'learn',   'icon' => 'ri-book-open-line',     'label' => 'Quero aprender sobre saúde mental'],
                            ['value' => 'explore', 'icon' => 'ri-compass-3-line',     'label' => 'Estou só a explorar'],
                        ];
                    @endphp

                    @foreach($intents as $opt)
                        <button type="button"
                                @click="intent = '{{ $opt['value'] }}'"
                                class="w-full flex items-center gap-4 p-4 rounded-2xl border-2 text-left transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                :class="intent === '{{ $opt['value'] }}'
                                    ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                                    : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/30 active:scale-[0.98]'">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                 :class="intent === '{{ $opt['value'] }}' ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400'">
                                <i class="{{ $opt['icon'] }} text-xl"></i>
                            </div>
                            <span class="text-sm font-bold transition-colors"
                                  :class="intent === '{{ $opt['value'] }}' ? 'text-indigo-800' : 'text-slate-700'">
                                {{ $opt['label'] }}
                            </span>
                            <i class="ri-check-line ml-auto text-indigo-500 transition-opacity"
                               :class="intent === '{{ $opt['value'] }}' ? 'opacity-100' : 'opacity-0'"></i>
                        </button>
                    @endforeach
                </div>

                <button type="button"
                        @click="next('intent')"
                        :disabled="!intent"
                        class="w-full mt-4 sm:mt-6 py-3 sm:py-3.5 rounded-2xl font-bold text-white transition-all duration-200 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] disabled:opacity-30 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                    Continuar
                </button>
            </div>

            {{-- ═══════════════════════════════
                 PASSO 2 — Estado emocional
            ═══════════════════════════════ --}}
            <div x-show="step === 2"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 style="display: none;">

                <div class="text-center mb-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">Como te sentes agora?</h1>
                    <p class="text-sm text-slate-500">Sem julgamentos. Sê honesto(a) contigo mesmo(a).</p>
                </div>

                @php
                    $moods = [
                        ['value' => '1', 'emoji' => '😔', 'label' => 'Muito em baixo'],
                        ['value' => '2', 'emoji' => '😞', 'label' => 'Em baixo'],
                        ['value' => '3', 'emoji' => '😐', 'label' => 'Neutro'],
                        ['value' => '4', 'emoji' => '🙂', 'label' => 'Bem'],
                        ['value' => '5', 'emoji' => '😊', 'label' => 'Muito bem'],
                    ];
                @endphp

                <div class="flex justify-center gap-2 sm:gap-4 mb-8 flex-wrap">
                    @foreach($moods as $opt)
                        <button type="button"
                                @click="mood = '{{ $opt['value'] }}'"
                                class="flex flex-col items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-2xl"
                                aria-label="{{ $opt['label'] }}">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl border-2 transition-all duration-200"
                                 :class="mood === '{{ $opt['value'] }}'
                                     ? 'border-indigo-500 bg-indigo-50 shadow-sm scale-110'
                                     : 'border-slate-200 bg-white hover:border-indigo-200 active:scale-95'">
                                {{ $opt['emoji'] }}
                            </div>
                            <span class="text-[10px] font-bold transition-colors leading-tight text-center max-w-[60px]"
                                  :class="mood === '{{ $opt['value'] }}' ? 'text-indigo-600' : 'text-slate-400'">
                                {{ $opt['label'] }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            @click="step = 1"
                            class="px-5 py-3.5 rounded-2xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                            aria-label="Voltar">
                        <i class="ri-arrow-left-line text-lg"></i>
                    </button>
                    <button type="button"
                            @click="next('mood')"
                            :disabled="!mood"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-white transition-all duration-200 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] disabled:opacity-30 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        Continuar
                    </button>
                </div>
            </div>

            {{-- ═══════════════════════════════
                 PASSO 3 — Preferência
            ═══════════════════════════════ --}}
            <div x-show="step === 3"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 style="display: none;">

                <div class="text-center mb-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">Como preferes expressar-te?</h1>
                    <p class="text-sm text-slate-500">Cada pessoa tem o seu ritmo.</p>
                </div>

                @php
                    $prefs = [
                        ['value' => 'read',   'icon' => 'ri-book-open-line',  'label' => 'Ler e refletir',    'desc' => 'Artigos e histórias'],
                        ['value' => 'listen', 'icon' => 'ri-headphone-line',  'label' => 'Ouvir e absorver',  'desc' => 'Música e silêncio'],
                        ['value' => 'talk',   'icon' => 'ri-chat-voice-line', 'label' => 'Falar e partilhar', 'desc' => 'Chat e fórum'],
                        ['value' => 'create', 'icon' => 'ri-pencil-line',     'label' => 'Escrever e criar',  'desc' => 'Diário e arte'],
                    ];
                @endphp

                <div class="grid grid-cols-2 gap-3">
                    @foreach($prefs as $opt)
                        <button type="button"
                                @click="preference = '{{ $opt['value'] }}'"
                                class="flex flex-col items-center text-center p-5 rounded-2xl border-2 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 active:scale-[0.97]"
                                :class="preference === '{{ $opt['value'] }}'
                                    ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                                    : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/30'">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 transition-colors"
                                 :class="preference === '{{ $opt['value'] }}' ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400'">
                                <i class="{{ $opt['icon'] }} text-2xl"></i>
                            </div>
                            <span class="text-sm font-bold transition-colors"
                                  :class="preference === '{{ $opt['value'] }}' ? 'text-indigo-800' : 'text-slate-700'">
                                {{ $opt['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1">{{ $opt['desc'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button"
                            @click="step = 2"
                            class="px-5 py-3.5 rounded-2xl font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition-colors active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                            aria-label="Voltar">
                        <i class="ri-arrow-left-line text-lg"></i>
                    </button>
                    <button type="submit"
                            :disabled="!preference || submitting"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-white transition-all duration-200 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] disabled:opacity-30 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        <span x-show="!submitting">Começar a minha jornada</span>
                        <span x-show="submitting" class="flex items-center justify-center gap-2">
                            <i class="ri-loader-4-line animate-spin"></i> A preparar...
                        </span>
                    </button>
                </div>
            </div>
        </form>

        {{-- Link de emergência sempre acessível --}}
        <div class="text-center mt-8 pb-4">
            <p class="text-xs text-slate-400">
                Em emergência?
                <a href="tel:112" class="text-rose-500 font-bold hover:underline focus-visible:outline-none">Liga o 112</a>
                ou
                <a href="tel:808242424" class="text-blue-500 font-bold hover:underline focus-visible:outline-none">SNS 24</a>
            </p>
        </div>
    </div>
</body>
</html>
