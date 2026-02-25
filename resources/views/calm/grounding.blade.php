<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grounding 5-4-3-2-1 | Lumina</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-teal-900 text-teal-50 min-h-screen flex flex-col font-sans selection:bg-teal-500 selection:text-white">

    <div x-data="{ step: 0 }" class="flex-1 flex flex-col items-center justify-center p-6 relative">
        
        <a href="{{ route('calm.index') }}" class="absolute top-6 left-6 text-teal-300 hover:text-white flex items-center gap-2 font-bold transition-colors">
            <i class="ri-arrow-left-line"></i> Sair
        </a>

        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0 flex items-center justify-center">
            <div class="w-[80vw] h-[80vw] md:w-[40vw] md:h-[40vw] bg-teal-600/10 rounded-full blur-[80px] animate-pulse"></div>
        </div>

        <div class="relative z-10 max-w-2xl w-full text-center">

            <div x-show="step === 0" x-transition.opacity.duration.500ms x-cloak class="space-y-8">
                <i class="ri-focus-2-line text-7xl text-teal-400"></i>
                <h1 class="text-4xl font-black text-white tracking-tight">Técnica de Grounding</h1>
                <p class="text-lg text-teal-200 max-w-lg mx-auto leading-relaxed">
                    A ansiedade puxa-nos para o futuro. O trauma prende-nos ao passado. 
                    Vamos usar os teus sentidos para trazer a tua mente de volta para o <strong>agora</strong>.
                </p>
                <button @click="step = 1" class="mt-8 px-8 py-4 bg-teal-500 hover:bg-teal-400 text-teal-950 font-black rounded-full text-lg shadow-lg shadow-teal-900/50 transition-all hover:scale-105">
                    Começar
                </button>
            </div>

            <div x-show="step === 1" x-transition.opacity.duration.800ms x-cloak class="space-y-8">
                <div class="text-9xl font-black text-teal-500/20 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10">5</div>
                <i class="ri-eye-line text-5xl text-teal-300"></i>
                <h2 class="text-3xl font-bold text-white">Olha em redor</h2>
                <p class="text-xl text-teal-100">Encontra e nomeia mentalmente <strong>5 coisas</strong> que consegues ver.</p>
                <p class="text-sm text-teal-400 italic">Ex: Uma caneta, uma sombra, um detalhe na parede...</p>
                <div class="pt-12">
                    <button @click="step = 2" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-teal-500/30 text-white font-bold rounded-full transition-all">
                        Já encontrei (Continuar)
                    </button>
                </div>
            </div>

            <div x-show="step === 2" x-transition.opacity.duration.800ms x-cloak class="space-y-8">
                <div class="text-9xl font-black text-teal-500/20 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10">4</div>
                <i class="ri-hand-coin-line text-5xl text-teal-300"></i>
                <h2 class="text-3xl font-bold text-white">Sente o ambiente</h2>
                <p class="text-xl text-teal-100">Toca e repara em <strong>4 coisas</strong> que consegues sentir.</p>
                <p class="text-sm text-teal-400 italic">Ex: A textura da tua roupa, a cadeira debaixo de ti, o chão nos teus pés...</p>
                <div class="pt-12">
                    <button @click="step = 3" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-teal-500/30 text-white font-bold rounded-full transition-all">
                        Já senti (Continuar)
                    </button>
                </div>
            </div>

            <div x-show="step === 3" x-transition.opacity.duration.800ms x-cloak class="space-y-8">
                <div class="text-9xl font-black text-teal-500/20 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10">3</div>
                <i class="ri-ear-line text-5xl text-teal-300"></i>
                <h2 class="text-3xl font-bold text-white">Escuta atentamente</h2>
                <p class="text-xl text-teal-100">Fecha os olhos um momento. Quais são as <strong>3 coisas</strong> que consegues ouvir?</p>
                <p class="text-sm text-teal-400 italic">Ex: O vento, um carro ao longe, a tua própria respiração...</p>
                <div class="pt-12">
                    <button @click="step = 4" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-teal-500/30 text-white font-bold rounded-full transition-all">
                        Já escutei (Continuar)
                    </button>
                </div>
            </div>

            <div x-show="step === 4" x-transition.opacity.duration.800ms x-cloak class="space-y-8">
                <div class="text-9xl font-black text-teal-500/20 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10">2</div>
                <i class="ri-windy-line text-5xl text-teal-300"></i>
                <h2 class="text-3xl font-bold text-white">Respira fundo</h2>
                <p class="text-xl text-teal-100">Tenta focar-te e identificar <strong>2 cheiros</strong> no ar.</p>
                <p class="text-sm text-teal-400 italic">Se não houver cheiro, cheira a tua pele, a tua roupa ou imagina o teu cheiro favorito.</p>
                <div class="pt-12">
                    <button @click="step = 5" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-teal-500/30 text-white font-bold rounded-full transition-all">
                        Já identifiquei (Continuar)
                    </button>
                </div>
            </div>

            <div x-show="step === 5" x-transition.opacity.duration.800ms x-cloak class="space-y-8">
                <div class="text-9xl font-black text-teal-500/20 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10">1</div>
                <i class="ri-cup-line text-5xl text-teal-300"></i>
                <h2 class="text-3xl font-bold text-white">O Paladar</h2>
                <p class="text-xl text-teal-100">Foca-te em <strong>1 coisa</strong> que consegues saborear agora.</p>
                <p class="text-sm text-teal-400 italic">Pode ser o sabor da pasta de dentes, de um café recente, ou bebe um golo de água.</p>
                <div class="pt-12">
                    <button @click="step = 6" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-teal-500/30 text-white font-bold rounded-full transition-all">
                        Terminar Exercício
                    </button>
                </div>
            </div>

            <div x-show="step === 6" x-transition.opacity.duration.800ms x-cloak class="space-y-8">
                <i class="ri-sun-fill text-7xl text-amber-300 animate-[spin_10s_linear_infinite]"></i>
                <h2 class="text-4xl font-black text-white">Estás aqui. Estás seguro.</h2>
                <p class="text-lg text-teal-200">
                    Sempre que a mente acelerar, lembra-te que os teus sentidos são a âncora para o momento presente.
                </p>
                <div class="pt-8 flex flex-col sm:flex-row justify-center gap-4">
                    <button @click="step = 1" class="px-6 py-3 border border-teal-400/50 text-teal-100 font-bold rounded-full hover:bg-teal-800 transition-colors">
                        Fazer Novamente
                    </button>
                    <a href="{{ route('calm.index') }}" class="px-6 py-3 bg-teal-500 hover:bg-teal-400 text-teal-950 font-black rounded-full shadow-lg transition-all text-center">
                        Voltar ao Santuário
                    </a>
                </div>
            </div>

        </div>

        <div x-show="step > 0 && step < 6" class="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-3">
            <template x-for="i in 5">
                <div class="w-2.5 h-2.5 rounded-full transition-all duration-300" :class="step >= i ? 'bg-teal-300 scale-110' : 'bg-teal-800'"></div>
            </template>
        </div>

    </div>

</body>
</html>