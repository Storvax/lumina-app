<x-lumina-layout title="Reflexo do Tempo | Lumina">
    
    <x-slot name="css">
        <style>
            /* Efeito de poeira estelar ao fundo */
            .stars-bg {
                background-image: 
                    radial-gradient(2px 2px at 20px 30px, #e2e8f0, rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 40px 70px, #ffffff, rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 50px 160px, #cbd5e1, rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 90px 40px, #f8fafc, rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 130px 80px, #e2e8f0, rgba(0,0,0,0));
                background-repeat: repeat;
                background-size: 200px 200px;
                animation: starsMove 100s linear infinite;
                opacity: 0.3;
            }
            @keyframes starsMove {
                from { transform: translateY(0); }
                to { transform: translateY(-200px); }
            }
            
            /* Scrollbar invisível no chat */
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
    </x-slot>

    {{-- Fundo Imersivo --}}
    <div class="fixed inset-0 bg-slate-950 -z-20 pointer-events-none"></div>
    <div class="fixed inset-0 bg-gradient-to-b from-indigo-900/20 via-slate-950 to-slate-950 -z-10 pointer-events-none"></div>
    <div class="fixed inset-0 stars-bg -z-10 pointer-events-none"></div>

    <div class="pt-24 pb-6 flex flex-col h-screen max-w-3xl mx-auto px-4 sm:px-6 relative" x-data="timeReflection()">
        
        {{-- Cabeçalho do Chat --}}
        <div class="flex items-center justify-between pb-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-4">
                <a href="{{ route('calm.index') }}" class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 flex items-center justify-center transition-colors">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-400 to-violet-600 flex items-center justify-center text-white text-xl shadow-[0_0_15px_rgba(99,102,241,0.5)]">
                            <i class="ri-sparkling-fill animate-pulse"></i>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-slate-950"></div>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white leading-tight">O Teu Eu do Futuro</h1>
                        <p class="text-xs text-indigo-300">A responder de daqui a 5 anos</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Área de Mensagens --}}
        <div class="flex-1 overflow-y-auto no-scrollbar py-6 space-y-6 flex flex-col" id="chat-container">
            
            {{-- Mensagem Inicial da IA --}}
            <div class="flex justify-start animate-fade-up">
                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl rounded-tl-sm bg-slate-800/80 border border-slate-700/50 p-4 text-slate-200 shadow-lg relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-indigo-400 to-violet-500"></div>
                    <p class="text-sm leading-relaxed">
                        Olá. Eu sei que as coisas parecem impossíveis agora. Mas eu sou tu, daqui a 5 anos. Queria dizer-te que nós conseguimos. Sobrevivemos a este momento. Fala comigo. O que te está a pesar tanto hoje?
                    </p>
                    <p class="text-[10px] text-slate-500 mt-2 font-medium">Hoje, 2031</p>
                </div>
            </div>

            {{-- Loop de Mensagens Dinâmicas --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex w-full animate-fade-up" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    
                    {{-- Balão do Utilizador --}}
                    <div x-show="msg.role === 'user'" class="max-w-[85%] sm:max-w-[75%] rounded-2xl rounded-tr-sm bg-indigo-600 p-4 text-white shadow-lg shadow-indigo-900/20">
                        <p class="text-sm leading-relaxed" x-text="msg.content"></p>
                        <p class="text-[10px] text-indigo-200 mt-2 text-right font-medium">Agora</p>
                    </div>

                    {{-- Balão do Futuro (IA) --}}
                    <div x-show="msg.role === 'ai'" class="max-w-[85%] sm:max-w-[75%] rounded-2xl rounded-tl-sm bg-slate-800/80 border border-slate-700/50 p-4 text-slate-200 shadow-lg relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-indigo-400 to-violet-500"></div>
                        <p class="text-sm leading-relaxed" x-text="msg.content"></p>
                        <p class="text-[10px] text-slate-500 mt-2 font-medium">Daqui a 5 anos</p>
                    </div>

                </div>
            </template>

            {{-- Indicador de Escrita (A Pensar no tempo) --}}
            <div x-show="isTyping" class="flex justify-start animate-fade-up" x-transition>
                <div class="rounded-2xl rounded-tl-sm bg-slate-800/80 border border-slate-700/50 py-3 px-5 shadow-lg relative">
                    <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-indigo-400 to-violet-500"></div>
                    <div class="flex items-center gap-1.5 h-5">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0s"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
            
            {{-- Âncora para scroll automático --}}
            <div id="scroll-anchor" class="h-1"></div>
        </div>

        {{-- Input Area --}}
        <div class="pt-4 shrink-0 pb-safe">
            <form @submit.prevent="sendMessage()" class="relative flex items-end gap-2">
                <textarea x-model="newMessage" 
                          x-ref="messageInput"
                          @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                          :disabled="isTyping"
                          rows="1"
                          placeholder="Escreve para o teu eu do futuro..." 
                          class="w-full bg-slate-900 border border-slate-700 text-slate-200 rounded-3xl pl-5 pr-14 py-3.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none no-scrollbar disabled:opacity-50 transition-all"></textarea>
                
                <button type="submit" 
                        :disabled="newMessage.trim().length === 0 || isTyping"
                        class="absolute right-2 bottom-2 w-10 h-10 rounded-full flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="newMessage.trim().length > 0 ? 'bg-indigo-600 text-white hover:bg-indigo-500 hover:scale-105' : 'bg-slate-800 text-slate-500'">
                    <i class="ri-send-plane-fill"></i>
                </button>
            </form>
            <p class="text-center text-[10px] text-slate-600 mt-3 flex items-center justify-center gap-1">
                <i class="ri-lock-line"></i> Conversa processada de forma anónima e apagada após a sessão.
            </p>
        </div>
    </div>

    <script>
        function timeReflection() {
            return {
                newMessage: '',
                isTyping: false,
                messages: [],

                scrollToBottom() {
                    setTimeout(() => {
                        const anchor = document.getElementById('scroll-anchor');
                        if(anchor) anchor.scrollIntoView({ behavior: 'smooth' });
                    }, 50);
                },

                async sendMessage() {
                    const text = this.newMessage.trim();
                    if (text === '' || this.isTyping) return;

                    // Adiciona a mensagem do utilizador ao ecrã
                    this.messages.push({ role: 'user', content: text });
                    this.newMessage = '';
                    this.isTyping = true;
                    this.scrollToBottom();

                    try {
                        // Envia para o Backend (onde a API da OpenAI vai atuar)
                        const response = await axios.post('{{ route('calm.reflection.send') ?? '#' }}', {
                            message: text,
                            history: this.messages // Enviamos o histórico para a IA ter contexto
                        });

                        // Adiciona a resposta da IA
                        this.messages.push({ role: 'ai', content: response.data.reply });
                    } catch (error) {
                        this.messages.push({ role: 'ai', content: "Desculpa, a nossa ligação temporal falhou um pouco. Podes repetir?" });
                    } finally {
                        this.isTyping = false;
                        this.scrollToBottom();
                        setTimeout(() => { this.$refs.messageInput.focus(); }, 100);
                    }
                }
            }
        }
    </script>
</x-lumina-layout>
