<section id="calma" class="py-24 bg-calm-50 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-white/40 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-teal-200/20 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Zona de Calma Imediata</h2>
            <p class="text-slate-500">Ferramentas para usares agora, se estiveres a sentir-te sobrecarregado.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-6 text-white text-center shadow-xl shadow-indigo-200 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
                
                <div class="relative z-10" id="breathe-widget">
                    <div class="flex justify-between items-start mb-4">
                        <i class="ri-lungs-line text-2xl opacity-80"></i>
                        <span class="text-[10px] bg-white/20 px-2 py-1 rounded-full uppercase tracking-wider font-bold">Calma</span>
                    </div>

                    <div class="relative w-32 h-32 mx-auto mb-6 flex items-center justify-center">
                        <div id="breathe-ring-1" class="absolute inset-0 border-4 border-white/10 rounded-full transition-all duration-[4000ms]"></div>
                        <div id="breathe-ring-2" class="absolute inset-4 border-4 border-white/20 rounded-full transition-all duration-[4000ms]"></div>
                        <div id="breathe-circle" class="relative z-10 w-16 h-16 bg-white text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg shadow-2xl transition-all duration-[4000ms] cursor-pointer hover:scale-105" onclick="toggleBreathing()">
                            <i class="ri-play-fill text-2xl" id="breathe-icon"></i>
                            <span id="breathe-text" class="hidden text-xs">4s</span>
                        </div>
                    </div>
                    
                    <h4 id="breathe-instruction" class="font-bold text-lg mb-1">Precisas de uma pausa?</h4>
                    <p id="breathe-sub" class="text-xs text-indigo-200">Clica no círculo para começar.</p>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="font-bold text-slate-800"><i class="ri-headphone-line text-primary-500 mr-2"></i> Sons de Portugal</h3>
                <div class="grid gap-4">
                    <button class="sound-btn flex items-center justify-between p-4 bg-white hover:bg-primary-50 rounded-2xl border border-slate-100 transition-all group" data-sound-name="Chuva na Serra da Estrela" data-sound="{{ asset('sounds/chuva.mp3') }}">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center"><i class="ri-drop-line"></i></div>
                            <div class="text-left">
                                <h4 class="font-bold text-slate-700 group-hover:text-primary-600">Chuva na Serra da Estrela</h4>
                                <p class="text-xs text-slate-400">Som contínuo • 30 min</p>
                            </div>
                        </div>
                        <i class="play-icon ri-play-circle-fill text-3xl text-slate-200 group-hover:text-primary-500 transition-colors"></i>
                    </button>
                    </div>
            </div>
        </div>
    </div>
</section>