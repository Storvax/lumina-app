<x-guest-layout>
    <div x-data="{ 
            step: 1, 
            expectation: '', 
            feeling: '', 
            aura: '',
            preference: '',
            nextStep() { 
                if (this.step === 1 && !this.expectation) return;
                if (this.step === 2 && !this.feeling) return;
                if (this.step === 3 && !this.aura) return;
                if (this.step === 4 && !this.preference) return;
                this.step++; 
            },
            prevStep() { if (this.step > 1) this.step--; }
        }" 
        class="w-full max-w-md mx-auto relative min-h-[500px] flex flex-col transition-colors duration-700"
        :class="{
            'bg-teal-50/50 dark:bg-teal-900/10': aura === 'calm',
            'bg-emerald-50/50 dark:bg-emerald-900/10': aura === 'hope',
            'bg-rose-50/50 dark:bg-rose-900/10': aura === 'warm'
        }">
        
        <div class="mb-8 relative h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="absolute top-0 left-0 h-full bg-indigo-500 transition-all duration-500 ease-out" :style="'width: ' + ((step / 5) * 100) + '%'"></div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="flex-1 flex flex-col">
            @csrf
            <input type="hidden" name="expectation" :value="expectation">
            <input type="hidden" name="feeling" :value="feeling">
            <input type="hidden" name="aura" :value="aura">
            <input type="hidden" name="preference" :value="preference">

            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="flex-1">
                <div class="text-center mb-6">
                    <h2 class="text-3xl font-black text-slate-800 dark:text-white mb-2">Bem-vindo(a) à Lumina 🌱</h2>
                    <p class="text-slate-500 dark:text-slate-400">O que esperas encontrar nesta comunidade?</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="expectation = 'support'; nextStep()" class="p-4 rounded-2xl border-2 transition-all text-left" :class="expectation === 'support' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <i class="ri-heart-pulse-fill text-2xl text-rose-500 mb-2 block"></i>
                        <span class="font-bold text-sm block">Apoio Emocional</span>
                    </button>
                    <button type="button" @click="expectation = 'share'; nextStep()" class="p-4 rounded-2xl border-2 transition-all text-left" :class="expectation === 'share' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <i class="ri-chat-1-fill text-2xl text-blue-500 mb-2 block"></i>
                        <span class="font-bold text-sm block">Partilhar Desabafos</span>
                    </button>
                    <button type="button" @click="expectation = 'listen'; nextStep()" class="p-4 rounded-2xl border-2 transition-all text-left" :class="expectation === 'listen' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <i class="ri-ear-fill text-2xl text-teal-500 mb-2 block"></i>
                        <span class="font-bold text-sm block">Ouvir e Ajudar</span>
                    </button>
                    <button type="button" @click="expectation = 'learn'; nextStep()" class="p-4 rounded-2xl border-2 transition-all text-left" :class="expectation === 'learn' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <i class="ri-book-open-fill text-2xl text-amber-500 mb-2 block"></i>
                        <span class="font-bold text-sm block">Aprender a Lidar</span>
                    </button>
                </div>
            </div>

            <div x-show="step === 2" style="display:none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="flex-1">
                <div class="text-center mb-6">
                    <button type="button" @click="prevStep()" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 mx-auto mb-4 hover:text-indigo-500"><i class="ri-arrow-left-line"></i></button>
                    <h2 class="text-2xl font-black mb-2">Compreendemos.</h2>
                    <p class="text-slate-500 text-sm">Como descreverias o que estás a sentir agora?</p>
                </div>
                <div class="space-y-3">
                    <button type="button" @click="feeling = 'overwhelmed'; nextStep()" class="w-full p-4 rounded-2xl border-2 transition-all flex items-center gap-4" :class="feeling === 'overwhelmed' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <span class="text-3xl">🌊</span><span class="font-bold">Sobrecarregado(a)</span>
                    </button>
                    <button type="button" @click="feeling = 'anxious'; nextStep()" class="w-full p-4 rounded-2xl border-2 transition-all flex items-center gap-4" :class="feeling === 'anxious' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <span class="text-3xl">🌩️</span><span class="font-bold">Ansioso(a) ou Inquieto(a)</span>
                    </button>
                    <button type="button" @click="feeling = 'lonely'; nextStep()" class="w-full p-4 rounded-2xl border-2 transition-all flex items-center gap-4" :class="feeling === 'lonely' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <span class="text-3xl">🌫️</span><span class="font-bold">Sozinho(a)</span>
                    </button>
                </div>
            </div>

            <div x-show="step === 3" style="display:none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" class="flex-1">
                <div class="text-center mb-6">
                    <button type="button" @click="prevStep()" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 mx-auto mb-4 hover:text-indigo-500"><i class="ri-arrow-left-line"></i></button>
                    <h2 class="text-2xl font-black mb-2">Escolhe a tua Aura</h2>
                    <p class="text-slate-500 text-sm">Que energia procuras cultivar hoje?</p>
                </div>
                <div class="space-y-4">
                    <button type="button" @click="aura = 'calm'; nextStep()" class="w-full p-5 rounded-[2rem] border-2 transition-all flex items-center gap-4 text-left group overflow-hidden relative" :class="aura === 'calm' ? 'border-teal-500 ring-4 ring-teal-500/20' : 'border-transparent bg-gradient-to-r from-teal-50 to-cyan-50 opacity-70 hover:opacity-100'">
                        <div class="w-12 h-12 rounded-full bg-teal-500 flex items-center justify-center text-white text-xl shadow-lg shrink-0"><i class="ri-drop-line"></i></div>
                        <div><h3 class="font-bold text-teal-900">Serenidade</h3><p class="text-xs text-teal-700/70">Abrandar e respirar fundo.</p></div>
                    </button>
                    <button type="button" @click="aura = 'hope'; nextStep()" class="w-full p-5 rounded-[2rem] border-2 transition-all flex items-center gap-4 text-left group overflow-hidden relative" :class="aura === 'hope' ? 'border-emerald-500 ring-4 ring-emerald-500/20' : 'border-transparent bg-gradient-to-r from-emerald-50 to-green-50 opacity-70 hover:opacity-100'">
                        <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xl shadow-lg shrink-0"><i class="ri-seedling-line"></i></div>
                        <div><h3 class="font-bold text-emerald-900">Esperança</h3><p class="text-xs text-emerald-700/70">Procurar a luz ao fundo do túnel.</p></div>
                    </button>
                    <button type="button" @click="aura = 'warm'; nextStep()" class="w-full p-5 rounded-[2rem] border-2 transition-all flex items-center gap-4 text-left group overflow-hidden relative" :class="aura === 'warm' ? 'border-rose-500 ring-4 ring-rose-500/20' : 'border-transparent bg-gradient-to-r from-rose-50 to-orange-50 opacity-70 hover:opacity-100'">
                        <div class="w-12 h-12 rounded-full bg-rose-500 flex items-center justify-center text-white text-xl shadow-lg shrink-0"><i class="ri-fire-line"></i></div>
                        <div><h3 class="font-bold text-rose-900">Acolhimento</h3><p class="text-xs text-rose-700/70">Procurar ou dar calor humano.</p></div>
                    </button>
                </div>
            </div>

            <div x-show="step === 4" style="display:none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" class="flex-1">
                <div class="text-center mb-6">
                    <button type="button" @click="prevStep()" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 mx-auto mb-4 hover:text-indigo-500"><i class="ri-arrow-left-line"></i></button>
                    <h2 class="text-2xl font-black mb-2">Não estás sozinho(a).</h2>
                    <p class="text-slate-500 text-sm">O que te faria sentir melhor neste momento?</p>
                </div>
                <div class="space-y-3">
                    <button type="button" @click="preference = 'read_write'; nextStep()" class="w-full p-4 rounded-2xl border-2 transition-all flex justify-between items-center" :class="preference === 'read_write' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <span class="font-bold flex items-center gap-2"><i class="ri-quill-pen-line text-xl"></i> Ler e Escrever</span> <i class="ri-arrow-right-s-line"></i>
                    </button>
                    <button type="button" @click="preference = 'listen'; nextStep()" class="w-full p-4 rounded-2xl border-2 transition-all flex justify-between items-center" :class="preference === 'listen' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <span class="font-bold flex items-center gap-2"><i class="ri-headphone-line text-xl"></i> Ouvir e Relaxar</span> <i class="ri-arrow-right-s-line"></i>
                    </button>
                    <button type="button" @click="preference = 'talk'; nextStep()" class="w-full p-4 rounded-2xl border-2 transition-all flex justify-between items-center" :class="preference === 'talk' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100 bg-white hover:border-indigo-200'">
                        <span class="font-bold flex items-center gap-2"><i class="ri-chat-voice-line text-xl"></i> Falar com Alguém</span> <i class="ri-arrow-right-s-line"></i>
                    </button>
                </div>
            </div>

            <div x-show="step === 5" style="display:none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" class="flex-1 flex flex-col">
                <div class="text-center mb-8">
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4"><i class="ri-home-heart-fill"></i></div>
                    <h2 class="text-2xl font-black text-slate-800">Tudo pronto!</h2>
                    <p class="text-slate-500 text-sm mt-1">Cria a tua conta para entrarmos no teu refúgio.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <x-text-input class="block w-full rounded-2xl bg-white border-slate-200 py-3" type="text" name="name" required placeholder="Como gostas de ser chamado(a)?" />
                    </div>
                    <div>
                        <x-text-input class="block w-full rounded-2xl bg-white border-slate-200 py-3" type="email" name="email" required placeholder="O teu Email seguro" />
                    </div>
                    <div>
                        <x-text-input class="block w-full rounded-2xl bg-white border-slate-200 py-3" type="password" name="password" required placeholder="Uma Password forte" />
                    </div>
                    <div>
                        <x-text-input class="block w-full rounded-2xl bg-white border-slate-200 py-3" type="password" name="password_confirmation" required placeholder="Confirma a Password" />
                    </div>
                </div>
                <div class="flex items-center justify-between mt-auto pt-8">
                    <button type="button" @click="prevStep()" class="text-slate-400 hover:text-slate-600 font-bold text-sm"><i class="ri-arrow-left-line"></i> Voltar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/30 flex items-center gap-2">
                        Criar Refúgio <i class="ri-check-line"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>