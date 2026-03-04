<x-lumina-layout title="O Teu Casulo | Lumina">
    
    <div class="py-12 pt-28 md:pt-32 relative">
        <div class="max-w-4xl mx-auto px-6">

            <x-emotional-breadcrumb :items="[['label' => 'Comunidade', 'route' => 'forum.index'], ['label' => 'O Teu Casulo']]" />

            {{-- Cabeçalho do Casulo --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 mt-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 text-[10px] font-black uppercase tracking-widest mb-4">
                        <i class="ri-shield-user-fill"></i> Grupo Restrito • 12 Membros
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                        Casulo da <span class="text-violet-500">Resiliência</span>
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-base mt-2">Um espaço pequeno para laços grandes. Aqui, todos conhecem a tua história.</p>
                </div>

                {{-- Rostos do Casulo (Avatares) --}}
                <div class="flex -space-x-3">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="w-12 h-12 rounded-full border-4 border-slate-50 dark:border-slate-900 bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-500 shadow-sm overflow-hidden group hover:-translate-y-2 transition-transform cursor-help">
                            <span class="group-hover:hidden">{{ chr(64 + $i) }}</span>
                            <i class="ri-heart-fill hidden group-hover:block text-rose-500"></i>
                        </div>
                    @endfor
                    <div class="w-12 h-12 rounded-full border-4 border-slate-50 dark:border-slate-900 bg-violet-500 flex items-center justify-center text-[10px] font-black text-white shadow-sm italic">
                        +7
                    </div>
                </div>
            </div>

            {{-- ALERTA DE ALTRUÍSMO (O "Desafio Secreto") --}}
            <div class="mb-10 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2rem] p-6 md:p-8 text-white shadow-xl shadow-indigo-900/20 relative overflow-hidden group">
                <i class="ri-sparkling-2-fill absolute -right-4 -top-4 text-8xl text-white/10 rotate-12 group-hover:rotate-45 transition-transform duration-1000"></i>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                    <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl animate-bounce-slight">
                        🫂
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold mb-1">Missão de Altruísmo Secreta</h3>
                        <p class="text-indigo-100 text-sm">O "Guardião da Chama B" registou uma noite difícil na Zona Calma. Que tal enviar um fôlego vocal anónimo para o encorajar ao amanhecer?</p>
                    </div>
                    <button class="px-6 py-3 bg-white text-indigo-600 font-black rounded-xl text-xs uppercase tracking-widest hover:bg-indigo-50 transition-colors shadow-lg">
                        Enviar Apoio
                    </button>
                </div>
            </div>

            {{-- Feed Intimista do Casulo --}}
            <div class="space-y-6">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-600 flex items-center gap-2">
                    <i class="ri-chat-3-line"></i> Conversas do Círculo
                </h2>

                {{-- Post do Casulo --}}
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">M</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">Membro M <span class="font-medium text-slate-400 mx-1">•</span> <span class="text-xs text-slate-400 font-medium">há 2 horas</span></p>
                        </div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed mb-4">
                        Hoje finalmente consegui sair de casa para uma caminhada de 10 minutos. Parece pouco, mas para mim foi uma vitória gigante. Obrigado pelas palavras de ontem, ajudaram muito.
                    </p>
                    <div class="flex items-center gap-4">
                        <button class="flex items-center gap-1.5 text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-900/30 px-3 py-1.5 rounded-lg hover:bg-rose-100 transition-colors">
                            🫂 <span class="ml-1">8 Abraços</span>
                        </button>
                        <button class="text-xs font-bold text-slate-400 hover:text-indigo-500 transition-colors">Responder</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-lumina-layout>