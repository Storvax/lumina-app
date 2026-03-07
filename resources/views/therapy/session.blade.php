<x-lumina-layout title="Sessão Clínica | Lumina PRO">
    
    {{-- Forçamos um fundo escuro para a sala de vídeo para reduzir fadiga visual --}}
    <div class="fixed inset-0 bg-slate-950 z-0"></div>

    <div class="relative z-10 py-12 pt-24 md:pt-28 h-screen flex flex-col max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header da Sessão --}}
        <div class="flex items-center justify-between mb-4 shrink-0 bg-slate-900/50 backdrop-blur-md border border-slate-800 p-4 rounded-2xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-teal-400 border border-teal-500/30">
                    <i class="ri-lock-2-fill"></i>
                </div>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">Sessão Segura (E2EE)</h2>
                    <p class="text-slate-400 text-xs">Dr(a). Ana Silva</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-3 py-1 bg-rose-500/20 text-rose-400 rounded-full text-xs font-bold flex items-center gap-2 border border-rose-500/30 animate-pulse">
                    <div class="w-2 h-2 rounded-full bg-rose-500"></div> REC
                </div>
                <span class="text-slate-300 font-mono text-sm font-bold">45:12</span>
            </div>
        </div>

        {{-- Área de Vídeo Principal --}}
        <div class="flex-1 flex flex-col md:flex-row gap-4 min-h-0 relative">
            
            {{-- Vídeo do Terapeuta (Falso para UI) --}}
            <div class="flex-1 bg-slate-900 rounded-3xl border border-slate-800 relative overflow-hidden flex items-center justify-center shadow-2xl">
                {{-- Aqui entraria a stream do Twilio/Daily.co --}}
                <i class="ri-user-smile-line text-7xl text-slate-800"></i>
                <div class="absolute bottom-4 left-4 px-3 py-1.5 bg-black/50 backdrop-blur-md rounded-lg text-white text-xs font-bold border border-white/10">
                    Dr(a). Ana Silva
                </div>
            </div>

            {{-- O teu Vídeo (Miniatura) --}}
            <div class="absolute top-6 right-6 w-32 md:w-48 aspect-[3/4] bg-slate-800 rounded-2xl border-2 border-slate-700 overflow-hidden shadow-2xl z-20 flex items-center justify-center">
                <i class="ri-user-line text-4xl text-slate-700"></i>
            </div>

            {{-- OVERLAY: Sincronia Somática (Oculto por defeito) --}}
            <div id="somatic-overlay" class="absolute inset-0 z-30 bg-rose-950/80 backdrop-blur-sm rounded-3xl flex flex-col items-center justify-center opacity-0 pointer-events-none transition-opacity duration-1000">
                <div class="relative flex items-center justify-center">
                    {{-- Ondas de pulsação visual --}}
                    <div id="pulse-wave-1" class="absolute w-32 h-32 bg-rose-500/20 rounded-full"></div>
                    <div id="pulse-wave-2" class="absolute w-48 h-48 bg-rose-500/10 rounded-full"></div>
                    
                    {{-- Ícone central --}}
                    <div id="heart-icon" class="relative z-10 w-20 h-20 bg-gradient-to-br from-rose-400 to-rose-600 rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(225,29,72,0.5)] transition-transform duration-100">
                        <i class="ri-heart-pulse-fill text-4xl text-white"></i>
                    </div>
                </div>
                
                <h3 class="text-white text-2xl font-black mt-8">Sincronia Somática Ativada</h3>
                <p class="text-rose-200/80 text-sm mt-2 max-w-sm text-center">O teu terapeuta ativou o Grounding. Encosta o telemóvel ao peito e respira ao ritmo da pulsação.</p>
                
                <button onclick="stopSomaticSync()" class="mt-8 px-6 py-2 rounded-full border border-rose-500/50 text-rose-300 text-xs font-bold hover:bg-rose-500/20 transition-colors">
                    Estou mais calmo(a) (Parar)
                </button>
            </div>
        </div>

        {{-- Controlos da Chamada --}}
        <div class="mt-4 shrink-0 flex items-center justify-center gap-4 bg-slate-900/50 backdrop-blur-md border border-slate-800 p-4 rounded-2xl">
            <button class="w-12 h-12 rounded-full bg-slate-700 text-white flex items-center justify-center hover:bg-slate-600 transition-colors"><i class="ri-mic-line text-xl"></i></button>
            <button class="w-12 h-12 rounded-full bg-slate-700 text-white flex items-center justify-center hover:bg-slate-600 transition-colors"><i class="ri-vidicon-line text-xl"></i></button>
            <button class="w-12 h-12 rounded-full bg-rose-600 text-white flex items-center justify-center hover:bg-rose-700 transition-colors shadow-lg shadow-rose-600/20"><i class="ri-phone-fill text-xl"></i></button>
            
            <div class="w-px h-8 bg-slate-700 mx-2"></div>
            
            {{-- BOTÃO DE TESTE (Simula a ação do terapeuta) --}}
            <button onclick="triggerSomaticSync()" class="px-4 py-2 bg-rose-950 text-rose-400 border border-rose-900 rounded-xl text-xs font-bold flex items-center gap-2 hover:bg-rose-900 transition-colors">
                <i class="ri-magic-line"></i> Simular Terapeuta
            </button>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            let somaticInterval = null;

            window.triggerSomaticSync = function() {
                const overlay = document.getElementById('somatic-overlay');
                const heart = document.getElementById('heart-icon');
                const wave1 = document.getElementById('pulse-wave-1');
                const wave2 = document.getElementById('pulse-wave-2');
                
                // Mostrar Overlay
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                
                // O padrão Haptic de um batimento cardíaco (Tum-Tum... pausa)
                // Array: [Vibra, Pausa, Vibra, Pausa Longa]
                const heartbeatPattern = [150, 150, 150, 550]; 

                // Loop a cada 1 segundo (60 bpm)
                somaticInterval = setInterval(() => {
                    // 1. Tentar vibrar o dispositivo (funciona em Android e iPhones com web app manifest)
                    if (navigator.vibrate) {
                        navigator.vibrate(heartbeatPattern);
                    }

                    // 2. Sincronizar UI (Primeiro Tum)
                    heart.style.transform = 'scale(1.2)';
                    wave1.classList.add('animate-ping');
                    
                    setTimeout(() => {
                        heart.style.transform = 'scale(1)';
                    }, 150);

                    // Sincronizar UI (Segundo Tum)
                    setTimeout(() => {
                        heart.style.transform = 'scale(1.3)';
                        wave2.classList.add('animate-ping');
                        
                        setTimeout(() => {
                            heart.style.transform = 'scale(1)';
                            wave1.classList.remove('animate-ping');
                            wave2.classList.remove('animate-ping');
                        }, 150);
                    }, 300);

                }, 1000); // Repete a cada 1000ms (60 BPM perfeitos)
            };

            window.stopSomaticSync = function() {
                const overlay = document.getElementById('somatic-overlay');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                
                if(somaticInterval) {
                    clearInterval(somaticInterval);
                    somaticInterval = null;
                }
                
                // Parar vibração ativa
                if (navigator.vibrate) navigator.vibrate(0); 
            };

            // Listener de WebSockets (A ser ativado quando o Claude fizer o backend)
            /*
            window.Echo.private(`session.${sessionId}`)
                .listen('SomaticSyncTriggered', (e) => {
                    window.triggerSomaticSync();
                });
            */
        </script>
    </x-slot>
</x-lumina-layout>