<x-lumina-layout title="Definições | Lumina">

    <x-slot name="css">
        <style>
            .active-tab { background-color: #f1f5f9; color: #4f46e5; border-left: 4px solid #4f46e5; }
            .inactive-tab { color: #64748b; border-left: 4px solid transparent; }
            .inactive-tab:hover { background-color: #f8fafc; color: #334155; }
            
            /* Inputs personalizados */
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
            <p class="text-slate-500">Gere as tuas informações, segurança e preferências de bem-estar.</p>
        </div>

        <div class="grid lg:grid-cols-4 gap-8">

            <div class="lg:col-span-1">
                <nav class="flex flex-col space-y-1 bg-white rounded-2xl shadow-sm border border-slate-100 p-2 sticky top-32">
                    
                    <button @click="currentTab = 'general'" 
                        :class="currentTab === 'general' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all">
                        <i class="ri-user-smile-line text-lg"></i>
                        <span>Perfil & Bio</span>
                    </button>

                    <button @click="currentTab = 'safety'" 
                        :class="currentTab === 'safety' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all">
                        <i class="ri-shield-heart-line text-lg"></i>
                        <span>Plano de Segurança</span>
                    </button>

                    <button @click="currentTab = 'security'" 
                        :class="currentTab === 'security' ? 'active-tab' : 'inactive-tab'"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-r-xl transition-all">
                        <i class="ri-lock-password-line text-lg"></i>
                        <span>Password & Conta</span>
                    </button>

                </nav>
            </div>

            <div class="lg:col-span-3">

                <div x-show="currentTab === 'general'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    
                    <form method="post" action="{{ route('profile.update') }}" class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white">
                        @csrf
                        @method('patch')

                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-20 h-20 rounded-full bg-indigo-50 border-2 border-indigo-100 p-1">
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
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-emerald-600 font-bold flex items-center gap-1">
                                    <i class="ri-check-line"></i> Guardado!
                                </p>
                            @endif
                            <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20">
                                Guardar Alterações
                            </button>
                        </div>
                    </form>
                </div>

                <div x-show="currentTab === 'safety'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    
                    <form method="post" action="{{ route('profile.update') }}" class="bg-white p-8 rounded-[2rem] shadow-xl shadow-rose-100/50 border border-white relative overflow-hidden">
                        @csrf
                        @method('patch')
                        
                        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-rose-400 to-rose-600"></div>

                        <div class="mb-8">
                            <h3 class="font-bold text-xl text-slate-800 flex items-center gap-2">
                                <i class="ri-shield-heart-fill text-rose-500"></i> Plano de Segurança
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">Estas informações são privadas e aparecem no teu perfil para te ajudar em momentos de crise.</p>
                        </div>

                        <div class="space-y-6">
                            @php 
                                // Garante que safety_plan é um array, mesmo se vier null da BD
                                $plan = is_array($user->safety_plan) ? $user->safety_plan : json_decode($user->safety_plan, true) ?? []; 
                            @endphp

                            <div class="bg-rose-50/50 p-6 rounded-2xl border border-rose-100">
                                <label class="block text-sm font-bold text-rose-900 mb-2">1. Sinais de Aviso (Gatilhos)</label>
                                <p class="text-xs text-rose-700/70 mb-3">O que acontece antes de uma crise? (ex: deixar de dormir, irritabilidade)</p>
                                <textarea name="safety_plan[triggers]" rows="3" class="w-full border-none rounded-xl bg-white focus:ring-2 focus:ring-rose-200 text-slate-700" placeholder="Escreve aqui...">{{ $plan['triggers'] ?? '' }}</textarea>
                            </div>

                            <div class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100">
                                <label class="block text-sm font-bold text-indigo-900 mb-2">2. Estratégias de Coping</label>
                                <p class="text-xs text-indigo-700/70 mb-3">O que podes fazer sozinho para te acalmar? (ex: ouvir música, banho frio)</p>
                                <textarea name="safety_plan[coping]" rows="3" class="w-full border-none rounded-xl bg-white focus:ring-2 focus:ring-indigo-200 text-slate-700" placeholder="Escreve aqui...">{{ $plan['coping'] ?? '' }}</textarea>
                            </div>

                            <div class="bg-emerald-50/50 p-6 rounded-2xl border border-emerald-100">
                                <label class="block text-sm font-bold text-emerald-900 mb-2">3. Pessoas de Confiança</label>
                                <p class="text-xs text-emerald-700/70 mb-3">Quem podes contactar? (Nome e Telefone)</p>
                                <textarea name="safety_plan[contacts]" rows="3" class="w-full border-none rounded-xl bg-white focus:ring-2 focus:ring-emerald-200 text-slate-700" placeholder="Escreve aqui...">{{ $plan['contacts'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-8 pt-6">
                            <button type="submit" class="bg-rose-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-rose-700 transition-all shadow-lg shadow-rose-600/20">
                                Atualizar Plano
                            </button>
                        </div>
                    </form>
                </div>

                <div x-show="currentTab === 'security'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    
                    <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white mb-8">
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

                    <div class="bg-rose-50 p-8 rounded-[2rem] border border-rose-100">
                        <h3 class="font-bold text-lg text-rose-900 mb-2">Zona de Perigo</h3>
                        <p class="text-sm text-rose-800/70 mb-6">Uma vez que apagues a tua conta, todos os teus dados (diários, posts, perfil) serão eliminados permanentemente.</p>
                        
                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="bg-rose-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-rose-700 transition-all shadow-lg shadow-rose-600/20 w-full md:w-auto">
                            Apagar Conta
                        </button>
                    </div>
                    
                    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                            @csrf
                            @method('delete')
                            <h2 class="text-lg font-medium text-gray-900">Tens a certeza?</h2>
                            <p class="mt-1 text-sm text-gray-600">Esta ação é irreversível.</p>
                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                <x-danger-button class="ml-3">Apagar Conta</x-danger-button>
                            </div>
                        </form>
                    </x-modal>

                </div>

            </div>
        </div>
    </div>

</x-lumina-layout>