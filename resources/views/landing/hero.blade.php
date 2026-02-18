<section id="inicio" class="relative pt-40 pb-24 overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full mesh-gradient opacity-60 -z-10"></div>
    
    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
        
        <div class="space-y-8 animate-fade-up">
            
            @if(Auth::check() && isset($userMood) && isset($moodSuggestion))
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/80 border border-indigo-100 shadow-sm backdrop-blur-md mb-4 animate-fade-in-down">
                    <div class="w-10 h-10 rounded-full bg-{{ $moodSuggestion['color'] }}-100 flex items-center justify-center text-{{ $moodSuggestion['color'] }}-600 shadow-inner">
                        <i class="{{ $moodSuggestion['icon'] }} text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Bem-vindo de volta, {{ Auth::user()->name }}</p>
                        <p class="text-xs md:text-sm font-medium text-slate-700 leading-tight mt-0.5">
                            Último registo: <span class="capitalize font-bold text-{{ $moodSuggestion['color'] }}-600">{{ $userMood }}</span>. 
                            <a href="{{ $moodSuggestion['link'] }}" class="underline decoration-{{ $moodSuggestion['color'] }}-300 hover:text-{{ $moodSuggestion['color'] }}-700 transition-colors font-semibold">{{ $moodSuggestion['text'] }}</a>
                        </p>
                    </div>
                </div>
            @else
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/60 border border-white text-primary-600 text-xs font-bold uppercase tracking-wider shadow-sm backdrop-blur-sm">
                    🚀 Versão Beta Disponível
                </div>
            @endif
            
            <h1 class="text-5xl md:text-6xl/tight font-extrabold text-slate-900 tracking-tight text-balance">
                Não tens de carregar o mundo <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-500 to-indigo-600">sozinho.</span>
            </h1>
            
            <p class="text-lg md:text-xl text-slate-500 font-light leading-relaxed max-w-lg">
                Um porto seguro digital. Entra em salas anónimas, desabafa com ouvintes treinados ou usa o nosso diário inteligente. Sem julgamentos.
            </p>

            <div class="bg-white/40 p-1 rounded-2xl border border-white/50 inline-block backdrop-blur-sm">
                <div class="glass-card p-5 rounded-xl flex flex-col gap-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Como te sentes hoje?</span>
                    <div class="flex gap-2 sm:gap-3 justify-center flex-wrap sm:flex-nowrap">
                        
                        <a href="{{ route('rooms.index') }}" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-amber-200 transition-all duration-300">
                            <i class="ri-thunderstorms-line text-2xl text-slate-400 group-hover:text-amber-500 transition-colors mb-1 sm:mb-0"></i>
                            <span class="text-[10px] font-bold text-amber-500 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Ansioso</span>
                        </a>
                        
                        <a href="{{ route('rooms.index') }}" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-slate-400 transition-all duration-300">
                            <i class="ri-cloud-off-line text-2xl text-slate-400 group-hover:text-slate-600 transition-colors mb-1 sm:mb-0"></i>
                            <span class="text-[10px] font-bold text-slate-600 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Triste</span>
                        </a>

                        <a href="{{ route('rooms.index') }}" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-rose-300 transition-all duration-300">
                            <i class="ri-fire-line text-2xl text-slate-400 group-hover:text-rose-500 transition-colors mb-1 sm:mb-0"></i>
                            <span class="text-[10px] font-bold text-rose-500 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Irritado</span>
                        </a>
                        
                        <a href="{{ route('rooms.index') }}" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-teal-300 transition-all duration-300">
                            <i class="ri-sun-line text-2xl text-slate-400 group-hover:text-teal-500 transition-colors mb-1 sm:mb-0"></i>
                            <span class="text-[10px] font-bold text-teal-500 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Bem</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <a href="{{ route('rooms.index') }}" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-primary-500 to-indigo-500 text-white font-semibold shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/40 hover:-translate-y-0.5 transition-all">
                    Entrar na Comunidade
                </a>
                <a href="#calma" class="px-6 py-4 rounded-2xl text-slate-600 font-medium hover:bg-white/50 transition-colors">
                    <i class="ri-play-circle-line align-bottom text-xl mr-1"></i> Ver vídeo
                </a>
            </div>
        </div>

        <div class="relative hidden lg:block animate-float">
            <div class="absolute -top-10 -right-10 w-72 h-72 bg-purple-200/50 rounded-full blur-3xl mix-blend-multiply"></div>
            <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-teal-200/50 rounded-full blur-3xl mix-blend-multiply"></div>
            
            <div class="glass-card p-6 rounded-3xl relative z-10 w-[85%] mx-auto rotate-[-2deg]">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-emerald-400 flex items-center justify-center text-white text-xs font-bold">PT</div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Sala: Ansiedade Social</p>
                        <p class="text-xs text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> 24 Online</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="bg-slate-50 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 shadow-sm max-w-[80%]">
                            Alguém acordado? Sinto-me super ansioso com a reunião de amanhã... 😰
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <div class="bg-primary-50 p-3 rounded-2xl rounded-tr-none text-sm text-primary-800 shadow-sm max-w-[80%]">
                            Estou aqui. Respira fundo. O que é que te preocupa mais?
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="bg-slate-50 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 shadow-sm max-w-[80%]">
                            Medo de bloquear quando for a minha vez de falar.
                        </div>
                    </div>
                </div>

                <div class="mt-6 relative">
                    <div class="w-full h-10 bg-slate-100 rounded-full"></div>
                    <div class="absolute right-1 top-1 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm"><i class="ri-send-plane-fill"></i></div>
                </div>
            </div>

            <div class="glass-card absolute -bottom-6 -left-4 p-4 rounded-2xl flex items-center gap-3 animate-[float_4s_ease-in-out_infinite_1s]">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center"><i class="ri-heart-pulse-fill"></i></div>
                <div>
                    <p class="text-xs text-slate-500 font-medium">Vidas tocadas</p>
                    <p class="text-lg font-bold text-slate-800">+1,240</p>
                </div>
            </div>
        </div>
    </div>
</section>