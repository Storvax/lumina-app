<x-lumina-layout title="Definições | Lumina">

    <x-slot name="css">
        <style>
            .active-tab { background-color: #f1f5f9; color: #4f46e5; border-left: 4px solid #4f46e5; }
            .inactive-tab { color: #64748b; border-left: 4px solid transparent; }
            .inactive-tab:hover { background-color: #f8fafc; color: #334155; }
            
            .lumina-input {
                width: 100%;
                border: 1px solid #e2e8f0;
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                transition: all 0.2s;
            }
            .lumina-input:focus {
                border-color: #6366f1;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
                outline: none;
            }
        </style>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10 pt-32" x-data="{ currentTab: 'general' }">
        
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-slate-900">Definições da Conta</h1>
            <p class="text-slate-500">Gere as tuas informações, segurança e dados.</p>
        </div>

        <div class="grid lg:grid-cols-4 gap-8">

            <div class="lg:col-span-1">
                <nav class="flex flex-col space-y-1 bg-white rounded-2xl shadow-sm border border-slate-100 p-2 md:sticky md:top-32">
                    
                    <button @click="currentTab = 'general'" 
                        :class="currentTab === 'general' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all text-left">
                        <i class="ri-user-smile-line text-lg"></i>
                        <span>Perfil & Bio</span>
                    </button>

                    <button @click="currentTab = 'safety'" 
                        :class="currentTab === 'safety' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all text-left">
                        <i class="ri-shield-heart-line text-lg"></i>
                        <span>Plano de Segurança</span>
                    </button>

                    <button @click="currentTab = 'privacy'" 
                        :class="currentTab === 'privacy' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all text-left">
                        <i class="ri-database-2-line text-lg"></i>
                        <span>Privacidade & Dados</span>
                    </button>

                    <button @click="currentTab = 'notifications'" 
                        :class="currentTab === 'notifications' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all text-left">
                        <i class="ri-notification-badge-line text-lg"></i>
                        <span>Notificações & Silêncio</span>
                    </button>

                    <button @click="currentTab = 'security'" 
                        :class="currentTab === 'security' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all text-left">
                        <i class="ri-lock-password-line text-lg"></i>
                        <span>Password & Conta</span>
                    </button>

                </nav>
            </div>

            <div class="lg:col-span-3">

                <div x-show="currentTab === 'general'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <form method="post" action="{{ route('profile.update') }}" class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white">
                        @csrf
                        @method('patch')

                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-20 h-20 rounded-full bg-indigo-50 border-2 border-indigo-100 p-1 shrink-0">
                                <img src="https://api.dicebear.com/7.x/notionists/svg?seed={{ $user->name }}" class="w-full h-full rounded-full object-cover">
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800">Foto de Perfil</h3>
                                <p class="text-xs text-slate-500 mb-2">Gerada automaticamente baseada no teu nome.</p>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nome de Exibição</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="lumina-input" required autofocus autocomplete="name">
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="lumina-input" required autocomplete="username">
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">A Tua História (Bio)</label>
                                <textarea name="bio" rows="4" class="lumina-input resize-none" placeholder="Partilha um pouco sobre a tua jornada...">{{ old('bio', $user->bio) }}</textarea>
                                <p class="text-xs text-slate-400 mt-2 text-right">Máximo 500 caracteres.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-slate-100">
                            @if (session('status') === 'profile-updated')
                                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="text-sm text-emerald-600 font-bold flex items-center gap-1">
                                    <i class="ri-check-line"></i> Guardado!
                                </p>
                            @endif
                            <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20">
                                Guardar Alterações
                            </button>
                        </div>
                    </form>
                </div>

                <div x-cloak x-show="currentTab === 'safety'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <form method="post" action="{{ route('profile.safety') }}" class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-xl shadow-rose-100/50 border border-white relative overflow-hidden">
                        @csrf
                        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-rose-400 to-rose-600"></div>

                        <div class="mb-8 mt-2">
                            <h3 class="font-bold text-xl text-slate-800 flex items-center gap-2">
                                <i class="ri-shield-heart-fill text-rose-500"></i> Plano de Segurança
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">Estas informações ajudam-te em momentos difíceis. Serão mostradas no Modo Crise.</p>
                        </div>

                        <div class="space-y-6">
                            @php 
                                $plan = is_array($user->safety_plan) ? $user->safety_plan : json_decode($user->safety_plan, true) ?? []; 
                            @endphp

                            <div class="bg-rose-50/50 p-5 sm:p-6 rounded-2xl border border-rose-100">
                                <label class="block text-sm font-bold text-rose-900 mb-2">1. Sinais de Aviso (Gatilhos)</label>
                                <p class="text-xs text-rose-700/70 mb-3">O que acontece antes de uma crise? (ex: deixar de dormir, isolamento)</p>
                                <textarea name="safety_plan[triggers]" rows="3" class="w-full border-none rounded-xl bg-white focus:ring-2 focus:ring-rose-200 text-slate-700" placeholder="Escreve aqui...">{{ $plan['triggers'] ?? '' }}</textarea>
                            </div>

                            <div class="bg-indigo-50/50 p-5 sm:p-6 rounded-2xl border border-indigo-100">
                                <label class="block text-sm font-bold text-indigo-900 mb-2">2. Estratégias de Coping</label>
                                <p class="text-xs text-indigo-700/70 mb-3">O que podes fazer para te acalmar? (ex: ouvir música X, banho frio)</p>
                                <textarea name="safety_plan[coping]" rows="3" class="w-full border-none rounded-xl bg-white focus:ring-2 focus:ring-indigo-200 text-slate-700" placeholder="Escreve aqui...">{{ $plan['coping'] ?? '' }}</textarea>
                            </div>

                            <div class="bg-emerald-50/50 p-5 sm:p-6 rounded-2xl border border-emerald-100">
                                <label class="block text-sm font-bold text-emerald-900 mb-2">3. Contactos de Confiança</label>
                                <p class="text-xs text-emerald-700/70 mb-3">Quem podes contactar e números de emergência.</p>
                                <textarea name="safety_plan[contacts]" rows="3" class="w-full border-none rounded-xl bg-white focus:ring-2 focus:ring-emerald-200 text-slate-700" placeholder="Escreve aqui...">{{ $plan['contacts'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <button type="submit" class="bg-rose-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-rose-700 transition-all shadow-lg shadow-rose-600/20">
                                Guardar Plano
                            </button>
                        </div>
                    </form>
                </div>

                <div x-cloak x-show="currentTab === 'privacy'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white mb-8">
                        <h3 class="font-bold text-xl text-slate-800 mb-2 flex items-center gap-2">
                            <i class="ri-file-download-line text-indigo-500"></i> Os Teus Dados
                        </h3>
                        <p class="text-sm text-slate-500 mb-6">De acordo com as políticas do RGPD, tens o direito a exportar todo o teu histórico da plataforma.</p>
                        
                        <form method="POST" action="{{ route('privacy.export') }}">
                            @csrf
                            <button type="submit" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-6 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2">
                                <i class="ri-download-cloud-2-line"></i> Exportar Histórico (JSON)
                            </button>
                        </form>
                    </div>

                    <div class="bg-amber-50 p-6 sm:p-8 rounded-[2rem] border border-amber-100">
                        <h3 class="font-bold text-lg text-amber-900 mb-2 flex items-center gap-2">
                            <i class="ri-zzz-line"></i> Pausar Conta (Hibernar)
                        </h3>
                        <p class="text-sm text-amber-800/80 mb-6">Precisas de um tempo fora? Podes hibernar a tua conta. O teu perfil ficará invisível, mas não perderás os teus registos de diário nem o teu progresso. Basta fazeres login para reativar.</p>
                        
                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-hibernation')" class="bg-amber-500 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20">
                            Hibernar Conta
                        </button>
                    </div>
                </div>

                <div x-cloak x-show="currentTab === 'notifications'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <form method="post" action="{{ route('profile.notifications') }}" class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white">
                        @csrf
                        @method('patch')

                        <div class="mb-8">
                            <h3 class="font-bold text-xl text-slate-800 flex items-center gap-2">
                                <i class="ri-moon-clear-line text-indigo-500"></i> O Teu Ritmo
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">A tecnologia deve servir-te, não controlar-te. Ajusta a forma como a Lumina comunica contigo.</p>
                        </div>

                        <div class="space-y-8">
                            <div class="bg-indigo-50/50 p-5 sm:p-6 rounded-2xl border border-indigo-100">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl shrink-0"><i class="ri-moon-fill"></i></div>
                                    <div>
                                        <h4 class="font-bold text-indigo-900">Horas de Silêncio</h4>
                                        <p class="text-xs text-indigo-700/70">Durante este período, receberás notificações na app, mas sem interrupções.</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row items-center gap-4">
                                    <div class="w-full sm:flex-1">
                                        <label class="block text-xs font-bold text-indigo-900 mb-1">Não incomodar a partir das:</label>
                                        <input type="time" name="quiet_hours_start" value="{{ $user->quiet_hours_start ? \Carbon\Carbon::parse($user->quiet_hours_start)->format('H:i') : '' }}" class="w-full rounded-xl border-indigo-200 bg-white focus:ring-indigo-500">
                                    </div>
                                    <div class="w-full sm:flex-1">
                                        <label class="block text-xs font-bold text-indigo-900 mb-1">Até às:</label>
                                        <input type="time" name="quiet_hours_end" value="{{ $user->quiet_hours_end ? \Carbon\Carbon::parse($user->quiet_hours_end)->format('H:i') : '' }}" class="w-full rounded-xl border-indigo-200 bg-white focus:ring-indigo-500">
                                    </div>
                                </div>
                                <p class="text-[10px] text-indigo-400 mt-2 italic">* Deixa em branco para desativar.</p>
                            </div>

                            <div class="bg-amber-50/50 p-5 sm:p-6 rounded-2xl border border-amber-100 flex items-start gap-4 cursor-pointer" onclick="document.getElementById('wants_summary').click()">
                                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl shrink-0"><i class="ri-calendar-heart-fill"></i></div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-amber-900">Resumo Semanal (Wrapped)</h4>
                                        <input type="checkbox" id="wants_summary" name="wants_weekly_summary" value="1" {{ $user->wants_weekly_summary ? 'checked' : '' }} class="w-5 h-5 rounded border-amber-300 text-amber-500 focus:ring-amber-500 mt-1 pointer-events-none">
                                    </div>
                                    <p class="text-sm text-amber-800/70 mt-1">Recebe uma notificação ao Domingo a celebrar os teus progressos da semana (Quantas vezes respiraste, abraços recebidos, etc).</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-slate-100">
                            @if (session('status') === 'notification-prefs-updated')
                                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-600 font-bold flex items-center gap-1">
                                    <i class="ri-check-line"></i> Preferências guardadas!
                                </p>
                            @endif
                            <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20">
                                Guardar Ritmo
                            </button>
                        </div>
                    </form>
                </div>

                <div x-cloak x-show="currentTab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    
                    <div class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white mb-8">
                        <h3 class="font-bold text-xl text-slate-800 mb-6 flex items-center gap-2">
                            <i class="ri-key-2-line text-indigo-500"></i> Alterar Password
                        </h3>
                        
                        <form method="post" action="{{ route('password.update') }}" class="grid gap-6">
                            @csrf
                            @method('put')

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Password Atual</label>
                                <input type="password" name="current_password" class="lumina-input" autocomplete="current-password">
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nova Password</label>
                                <input type="password" name="password" class="lumina-input" autocomplete="new-password">
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Confirmar Nova Password</label>
                                <input type="password" name="password_confirmation" class="lumina-input" autocomplete="new-password">
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex justify-end pt-4">
                                @if (session('status') === 'password-updated')
                                    <p class="text-sm text-emerald-600 font-bold mr-4 flex items-center gap-1"><i class="ri-check-line"></i> Atualizada!</p>
                                @endif
                                <button type="submit" class="bg-white border border-slate-200 text-slate-700 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-50 transition-all">
                                    Mudar Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-rose-50 p-6 sm:p-8 rounded-[2rem] border border-rose-100">
                        <h3 class="font-bold text-lg text-rose-900 mb-2">Zona de Perigo</h3>
                        <p class="text-sm text-rose-800/70 mb-6">Uma vez que apagues a tua conta, todos os teus dados (diários, posts, perfil) serão eliminados permanentemente. Considera hibernar a conta em vez disso.</p>
                        
                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="bg-rose-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-rose-700 transition-all shadow-lg shadow-rose-600/20 w-full sm:w-auto">
                            Apagar Conta Definitivamente
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <x-modal name="confirm-user-hibernation" focusable>
        <form method="post" action="{{ route('privacy.hibernate') }}" class="p-8">
            @csrf
            <h2 class="text-xl font-bold text-slate-900 mb-2 flex items-center gap-2"><i class="ri-zzz-line text-amber-500"></i> Hibernar Conta</h2>
            <p class="text-sm text-slate-600 mb-6">A tua conta será desativada e ocultada da comunidade. Podes recuperá-la a qualquer momento bastando fazer login de novo. Por segurança, introduz a tua password.</p>
            
            <input type="password" name="password" class="lumina-input mb-4" placeholder="Password" required>
            <x-input-error :messages="$errors->userHibernation->get('password')" class="mb-4" />

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-colors">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold shadow-lg shadow-amber-500/30 transition-colors">Pausar a minha conta</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
            @csrf
            @method('delete')
            <h2 class="text-xl font-bold text-slate-900 mb-2">Apagar conta definitivamente?</h2>
            <p class="text-sm text-slate-600 mb-6">Esta ação é irreversível. Para tua segurança, introduz a password para confirmar.</p>
            
            <input type="password" name="password" class="lumina-input mb-4" placeholder="Password" required>
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mb-4" />

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-colors">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-lg shadow-rose-600/30 transition-colors">Sim, apagar conta</button>
            </div>
        </form>
    </x-modal>

</x-lumina-layout>