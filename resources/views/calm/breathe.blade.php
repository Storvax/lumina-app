<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Respiração Somática | Lumina</title>
    <meta name="theme-color" content="#020617">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans overflow-hidden selection:bg-teal-500/30 selection:text-teal-200"
      x-data="somaticBreathing()">

    {{-- Navegação de Saída --}}
    <div class="relative z-20 p-6 flex justify-between items-center transition-opacity duration-700" :class="isActive ? 'opacity-0 pointer-events-none' : 'opacity-100'">
        <a href="{{ route('calm.index') }}" class="text-slate-500 hover:text-white flex items-center gap-2 font-bold transition-colors">
            <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm">Sair do Exercício</span>
        </a>
        
        {{-- Toggle de Vibração --}}
        <button @click="toggleHaptics()" class="flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-full border transition-colors"
                :class="useHaptics ? 'border-teal-500/50 text-teal-400 bg-teal-500/10' : 'border-slate-800 text-slate-500 bg-slate-900'">
            <i :class="useHaptics ? 'ri-smartphone-line' : 'ri-smartphone-line opacity-50'"></i>
            <span x-text="useHaptics ? 'Vibração Ativa' : 'Vibração Inativa'"></span>
        </button>
    </div>

    <main class="flex-1 flex flex-col items-center justify-center relative z-10 w-full max-w-md mx-auto px-6">
        
        {{-- Título e Instruções (Desaparece ao começar) --}}
        <div class="text-center absolute top-10 w-full transition-opacity duration-700" :class="isActive ? 'opacity-0' : 'opacity-100'">
            <h1 class="text-2xl font-black text-white mb-2">Respiração Cega</h1>
            <p class="text-slate-400 text-sm max-w-xs mx-auto">Podes fechar os olhos. O teu telemóvel vai vibrar para te guiar. Inspira quando a vibração subir, expira quando descer.</p>
        </div>

        {{-- O Círculo Central de Respiração --}}
        <div class="relative w-64 h-64 flex items-center justify-center mt-10">
            {{-- Ondas de expansão --}}
            <div class="absolute inset-0 bg-teal-500/20 rounded-full blur-xl transition-all duration-1000 ease-in-out"
                 :class="circleScaleClass()"></div>
            
            <div class="absolute inset-4 bg-gradient-to-tr from-teal-600 to-emerald-400 rounded-full opacity-10 transition-all duration-1000 ease-in-out"
                 :class="circleScaleClass()"></div>

            {{-- Círculo Físico --}}
            <div class="relative z-10 w-32 h-32 rounded-full border-2 border-teal-500/50 flex items-center justify-center bg-slate-950 shadow-[0_0_50px_rgba(20,184,166,0.1)] transition-all duration-[4000ms] ease-in-out"
                 :style="getTransformStyle()">
                
                {{-- Texto de Fase --}}
                <span class="text-lg font-black tracking-widest uppercase transition-colors duration-500"
                      :class="phase === 'Inspira' ? 'text-teal-300' : (phase === 'Expira' ? 'text-emerald-500' : 'text-slate-400')"
                      x-text="phase"
                      style="transform: scale(calc(1 / var(--current-scale, 1))); transition: transform 0s;">
                </span>
            </div>
        </div>

        {{-- Botão de Iniciar / Parar --}}
        <div class="absolute bottom-20">
            <button @click="toggleSession()" 
                    class="px-10 py-4 rounded-full font-black text-sm tracking-widest uppercase transition-all duration-500 shadow-xl"
                    :class="isActive ? 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white' : 'bg-teal-600 text-white hover:bg-teal-500 hover:scale-105 hover:shadow-teal-500/20'">
                <span x-text="isActive ? 'Parar' : 'Começar'"></span>
            </button>
        </div>

    </main>

    <script>
        function somaticBreathing() {
            return {
                isActive: false,
                useHaptics: true,
                phase: 'Pronto', // Pronto, Inspira, Sustém, Expira
                timer: null,
                scale: 1,

                init() {
                    // Verifica se o telemóvel suporta vibração
                    if (!window.navigator || !window.navigator.vibrate) {
                        this.useHaptics = false;
                    }
                },

                toggleHaptics() {
                    this.useHaptics = !this.useHaptics;
                    if (this.useHaptics && window.navigator.vibrate) window.navigator.vibrate(50);
                },

                toggleSession() {
                    if (this.isActive) {
                        this.stopSession();
                    } else {
                        this.startSession();
                    }
                },

                startSession() {
                    this.isActive = true;
                    this.runBoxBreathing();
                },

                stopSession() {
                    this.isActive = false;
                    this.phase = 'Pronto';
                    this.scale = 1;
                    clearTimeout(this.timer);
                    if (window.navigator.vibrate) window.navigator.vibrate(0); // Para qualquer vibração
                },

                // Lógica de Box Breathing (4-4-4-4)
                async runBoxBreathing() {
                    if (!this.isActive) return;

                    // 1. INSPIRA (4 segundos)
                    this.phase = 'Inspira';
                    this.scale = 1.6;
                    if (this.useHaptics) this.vibrateInhale();
                    await this.delay(4000);
                    if (!this.isActive) return;

                    // 2. SUSTÉM (4 segundos)
                    this.phase = 'Sustém';
                    if (this.useHaptics) this.vibrateHold();
                    await this.delay(4000);
                    if (!this.isActive) return;

                    // 3. EXPIRA (4 segundos)
                    this.phase = 'Expira';
                    this.scale = 1;
                    if (this.useHaptics) this.vibrateExhale();
                    await this.delay(4000);
                    if (!this.isActive) return;

                    // 4. SUSTÉM VAZIO (4 segundos)
                    this.phase = 'Sustém';
                    await this.delay(4000);

                    // Ciclo infinito
                    if (this.isActive) this.runBoxBreathing();
                },

                delay(ms) {
                    return new Promise(resolve => {
                        this.timer = setTimeout(resolve, ms);
                    });
                },

                // Padrões de Vibração (Milissegundos: [Vibra, Pausa, Vibra, Pausa...])
                vibrateInhale() {
                    // Pulsos rápidos que simulam batimento cardíaco a acelerar
                    window.navigator.vibrate([50, 200, 50, 150, 50, 100, 100, 50, 150]);
                },

                vibrateHold() {
                    // Um pequeno "tap" para avisar que é para suster
                    window.navigator.vibrate([30]);
                },

                vibrateExhale() {
                    // Pulsos longos e espaçados, a abrandar
                    window.navigator.vibrate([150, 100, 100, 200, 50, 300, 50]);
                },

                getTransformStyle() {
                    return `transform: scale(${this.scale}); --current-scale: ${this.scale};`;
                },

                circleScaleClass() {
                    if (!this.isActive) return 'scale-100 opacity-20';
                    return this.scale > 1 ? 'scale-[1.8] opacity-60' : 'scale-100 opacity-20';
                }
            }
        }
    </script>
</body>
</html>