<x-lumina-layout title="Encontrar Apoio | Lumina">
    <div class="py-12 pt-28 md:pt-32 max-w-3xl mx-auto px-4 sm:px-6 h-[calc(100vh-2rem)] flex flex-col">
        
        {{-- Header da Triagem --}}
        <div class="text-center mb-6 shrink-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest mb-3 border border-indigo-100 dark:border-indigo-800">
                <i class="ri-robot-2-line"></i> Guia Lumina
            </div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">Dar o próximo passo.</h1>
            <p class="text-slate-500 text-sm mt-1">Uma conversa rápida para te conectarmos à pessoa certa.</p>
        </div>

        {{-- Interface de Chat (Alpine.js) --}}
        <div x-data="smartMatchChat()" class="flex-1 flex flex-col bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-xl overflow-hidden relative">
            
            {{-- Área de Mensagens --}}
            <div id="chat-container" class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        
                        {{-- Mensagem da IA --}}
                        <div x-show="msg.role === 'ai'" class="flex items-start gap-3 max-w-[85%]">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 mt-1">
                                <i class="ri-sparkling-fill"></i>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-600 text-slate-700 dark:text-slate-200 p-4 rounded-2xl rounded-tl-none text-sm leading-relaxed shadow-sm" x-html="msg.content"></div>
                        </div>

                        {{-- Mensagem do Utilizador --}}
                        <div x-show="msg.role === 'user'" class="bg-indigo-600 text-white p-4 rounded-2xl rounded-tr-none text-sm leading-relaxed shadow-md max-w-[85%]" x-text="msg.content"></div>
                    </div>
                </template>

                {{-- Indicador de "A Escrever..." --}}
                <div x-show="isLoading" class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                        <i class="ri-sparkling-fill animate-pulse"></i>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-600 p-4 rounded-2xl rounded-tl-none flex items-center gap-1.5 h-12">
                        <div class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0s;"></div>
                        <div class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>

                {{-- Cartões de Psicólogos (Aparecem no fim) --}}
                <div x-show="therapists.length > 0" class="mt-8 space-y-4 animate-fade-up">
                    <div class="text-center mb-6">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Encontrámos as tuas compatibilidades</span>
                    </div>
                    
                    <template x-for="therapist in therapists" :key="therapist.id">
                        <div class="bg-white dark:bg-slate-800 border-2 border-indigo-100 dark:border-indigo-900/30 p-5 rounded-2xl flex flex-col md:flex-row items-center gap-5 hover:border-indigo-300 transition-colors shadow-sm">
                            <img :src="therapist.avatar" alt="Avatar" class="w-16 h-16 rounded-full object-cover shadow-sm">
                            <div class="flex-1 text-center md:text-left">
                                <h4 class="font-black text-slate-800 dark:text-white text-lg" x-text="therapist.name"></h4>
                                <p class="text-indigo-600 dark:text-indigo-400 text-xs font-bold mb-2" x-text="therapist.specialty"></p>
                                <p class="text-slate-500 text-sm leading-snug" x-text="therapist.approach"></p>
                            </div>
                            <button class="px-5 py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 font-bold rounded-xl hover:bg-indigo-100 transition-colors shrink-0 text-sm">
                                Marcar Sessão
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Área de Input --}}
            <div class="p-4 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700 shrink-0">
                <form @submit.prevent="sendMessage" class="relative flex items-center">
                    <input type="text" x-model="userInput" :disabled="isLoading || matchFound" placeholder="Escreve a tua resposta aqui..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-full pl-6 pr-14 py-4 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all text-sm text-slate-700 dark:text-white disabled:opacity-50">
                    <button type="submit" :disabled="isLoading || !userInput.trim() || matchFound" class="absolute right-2 w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:hover:bg-indigo-600 focus:outline-none">
                        <i class="ri-send-plane-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function smartMatchChat() {
                return {
                    userInput: '',
                    isLoading: false,
                    matchFound: false,
                    therapists: [],
                    messages: [
                        { role: 'ai', content: 'Olá. Sei que dar este passo exige coragem, por isso agradeço por estares aqui. Podes dizer-me brevemente o que te traz a procurar ajuda? (Ex: ansiedade, dificuldades no trabalho, luto...)' }
                    ],
                    async sendMessage() {
                        if (!this.userInput.trim() || this.isLoading) return;
                        
                        const msgText = this.userInput;
                        this.messages.push({ role: 'user', content: msgText });
                        this.userInput = '';
                        this.isLoading = true;

                        this.scrollToBottom();

                        try {
                            const response = await axios.post('/terapia/triagem', {
                                history: this.messages
                            });

                            this.isLoading = false;
                            
                            // A IA responde com texto normal
                            if(response.data.reply) {
                                this.messages.push({ role: 'ai', content: response.data.reply });
                            }
                            
                            // Se a IA decidiu que já tem info suficiente, manda o array de psicólogos
                            if(response.data.therapists && response.data.therapists.length > 0) {
                                this.matchFound = true;
                                this.therapists = response.data.therapists;
                                this.messages.push({ role: 'ai', content: 'Obrigado por partilhares isso comigo. Com base no que me disseste, fiz o cruzamento com a nossa rede e encontrei estes profissionais que me parecem ter a abordagem ideal para ti:' });
                            }

                            this.scrollToBottom();
                        } catch (error) {
                            this.isLoading = false;
                            this.messages.push({ role: 'ai', content: 'Desculpa, tive um problema de ligação. Podes tentar enviar de novo?' });
                        }
                    },
                    scrollToBottom() {
                        setTimeout(() => {
                            const container = document.getElementById('chat-container');
                            container.scrollTop = container.scrollHeight;
                        }, 50);
                    }
                }
            }
        </script>
    </x-slot>
</x-lumina-layout>