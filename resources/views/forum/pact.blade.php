<x-lumina-layout title="O Diário do Pacto | Lumina">
    
    <x-slot name="css">
        <style>
            /* Efeito de Névoa Mágica para respostas bloqueadas */
            .fog-blur {
                filter: blur(8px);
                user-select: none;
                transition: filter 1.5s ease-out;
            }
            .unlocked .fog-blur {
                filter: blur(0);
                user-select: auto;
            }
        </style>
    </x-slot>

    {{-- Fundo Noturno e Intimista --}}
    <div class="fixed inset-0 bg-slate-950 -z-20 pointer-events-none"></div>
    <div class="fixed inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-violet-900/20 via-slate-950 to-slate-950 -z-10 pointer-events-none"></div>

    <div class="py-12 pt-28 md:pt-32 relative z-10" x-data="pactJournal()">
        <div class="max-w-3xl mx-auto px-6">

            {{-- Navegação --}}
            <div class="flex items-center justify-between mb-10">
                <a href="{{ route('forum.index') }}" class="text-violet-400/50 hover:text-violet-400 flex items-center gap-2 font-bold transition-colors">
                    <i class="ri-arrow-left-line text-lg"></i> <span class="text-sm">Voltar ao Casulo</span>
                </a>
                <div class="px-3 py-1 bg-violet-500/10 border border-violet-500/20 rounded-full text-violet-300 text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5">
                    <i class="ri-lock-unlock-line" x-show="hasAnswered"></i>
                    <i class="ri-lock-line" x-show="!hasAnswered"></i>
                    Pacto Diário
                </div>
            </div>

            {{-- A Pergunta do Dia --}}
            <div class="text-center mb-12 relative">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-violet-600/20 rounded-full blur-[60px] pointer-events-none"></div>
                <p class="text-violet-400 text-xs font-bold uppercase tracking-widest mb-4 inline-block relative z-10">Reflexão do Casulo</p>
                <h1 class="text-2xl md:text-4xl font-black text-white leading-tight relative z-10 font-serif italic">
                    "Qual foi a coisa mais difícil que tiveste de largar esta semana?"
                </h1>
            </div>

            {{-- Zona de Resposta do Utilizador --}}
            <div class="mb-16 relative z-20" x-show="!hasAnswered" x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-8">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl relative overflow-hidden focus-within:border-violet-500/50 transition-colors">
                    <form @submit.prevent="submitAnswer()">
                        <textarea x-model="myAnswer" rows="3" placeholder="Sê sincero. Só o teu Casulo vai ler, e de forma anónima..." class="w-full bg-transparent border-none text-slate-200 placeholder-slate-600 focus:ring-0 resize-none outline-none text-base md:text-lg"></textarea>
                        
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-800">
                            <p class="text-xs text-slate-500"><i class="ri-eye-off-line"></i> A tua identidade está protegida.</p>
                            <button type="submit" :disabled="myAnswer.trim().length < 5 || isSubmitting" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-500 text-white font-bold rounded-xl text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-[0_0_15px_rgba(124,58,237,0.3)]">
                                <span x-show="!isSubmitting">Partilhar e Revelar</span>
                                <i class="ri-loader-4-line animate-spin" x-show="isSubmitting" style="display: none;"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- A Minha Resposta (Aparece após submeter) --}}
            <div class="mb-12 text-center" x-show="hasAnswered" style="display: none;" x-transition:enter="transition ease-out duration-1000 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                <p class="text-xs font-bold text-violet-400 uppercase tracking-widest mb-3">A tua verdade</p>
                <p class="text-lg text-slate-300 italic" x-text="`&quot;${myAnswer}&quot;`"></p>
            </div>

            {{-- Galeria de Respostas do Casulo --}}
            <div class="space-y-4 relative" :class="hasAnswered ? 'unlocked' : ''">
                
                {{-- Overlay de Bloqueio (Só aparece se não respondeu) --}}
                <div class="absolute inset-0 z-30 flex flex-col items-center justify-center pointer-events-none" x-show="!hasAnswered" x-transition.opacity.duration.1000ms>
                    <div class="w-16 h-16 rounded-full bg-slate-900/80 backdrop-blur-xl border border-slate-700 text-slate-400 flex items-center justify-center text-2xl mb-4 shadow-2xl">
                        <i class="ri-lock-2-line"></i>
                    </div>
                    <p class="text-white font-bold text-lg mb-1">Respostas Ocultas</p>
                    <p class="text-slate-400 text-sm">Partilha a tua resposta para ler as dos outros.</p>
                </div>

                {{-- Cartões de Resposta (Falsos/Mock para UI) --}}
                <template x-for="answer in pactAnswers" :key="answer.id">
                    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 md:p-6 transition-all duration-1000">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-500 font-bold text-xs" x-text="answer.avatar"></div>
                            <p class="text-xs font-medium text-slate-500">Membro do Casulo <span class="mx-1">•</span> <span x-text="answer.time"></span></p>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed fog-blur" x-text="answer.text"></p>
                    </div>
                </template>
            </div>

        </div>
    </div>

    <script>
        function pactJournal() {
            return {
                myAnswer: '',
                isSubmitting: false,
                hasAnswered: false,
                pactAnswers: [
                    { id: 1, avatar: 'A', time: 'há 1 hora', text: 'Tive de aceitar que não consigo controlar as decisões da minha família. Doeu muito, chorei no carro antes de entrar em casa, mas hoje sinto-me 10kg mais leve.' },
                    { id: 2, array: 'M', time: 'há 3 horas', text: 'Largar a ideia de que tenho de ser super produtivo todos os dias. Esta semana não fiz quase nada, e tive de fazer as pazes com isso.' },
                    { id: 3, avatar: 'F', time: 'há 5 horas', text: 'Apagar o contacto de uma pessoa que me fazia mal. Fiquei a olhar para o ecrã durante meia hora antes de conseguir clicar em apagar.' }
                ],

                async submitAnswer() {
                    if (this.myAnswer.trim().length < 5) return;
                    this.isSubmitting = true;

                    // O Claude ligará isto à base de dados para guardar na tabela PactAnswers
                    // const res = await axios.post('/casulo/pacto/responder', { answer: this.myAnswer });

                    setTimeout(() => {
                        this.isSubmitting = false;
                        this.hasAnswered = true;
                        
                        // Vibração de desbloqueio para impacto psicológico
                        if(window.navigator && window.navigator.vibrate) window.navigator.vibrate([40, 60, 40]);
                        
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 1200);
                }
            }
        }
    </script>
</x-lumina-layout>