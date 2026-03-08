<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Criar Conta | Lumina</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-gradient {
            background-image:
                radial-gradient(at 40% 20%, hsla(260,100%,94%,1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(189,100%,92%,1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(341,100%,95%,1) 0px, transparent 50%);
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="mesh-gradient min-h-screen flex items-center justify-center p-6 selection:bg-indigo-500/30">

    {{-- Botão de Voltar Seguro --}}
    <div class="absolute top-6 left-6 z-20">
        <a href="{{ route('home') }}" class="flex items-center justify-center w-11 h-11 bg-white/50 backdrop-blur-sm rounded-full text-slate-500 hover:text-indigo-600 hover:bg-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
    </div>

    <div class="w-full max-w-5xl flex bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl overflow-hidden border border-white/50 animate-fade-up">
        
        {{-- Painel Visual (Esquerda) --}}
        <div class="hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden items-center justify-center p-12">
            <img src="{{ asset('images/register.jpg') }}" alt="Paz Interior" class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-overlay">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
            
            <div class="relative z-10 text-center">
                <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/20">
                    <i class="ri-leaf-line text-3xl text-white"></i>
                </div>
                <h2 class="text-3xl font-black text-white mb-4">O teu espaço seguro.</h2>
                <p class="text-indigo-100/80 leading-relaxed max-w-sm mx-auto">
                    Um lugar sem julgamentos para organizares a tua mente, acompanhares as tuas emoções e encontrares a paz no meio do caos.
                </p>
                
                <div class="mt-12 flex items-center justify-center gap-4">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full border-2 border-slate-900 bg-indigo-500 flex items-center justify-center text-white font-bold text-xs">M</div>
                        <div class="w-10 h-10 rounded-full border-2 border-slate-900 bg-emerald-500 flex items-center justify-center text-white font-bold text-xs">J</div>
                        <div class="w-10 h-10 rounded-full border-2 border-slate-900 bg-rose-500 flex items-center justify-center text-white font-bold text-xs">A</div>
                    </div>
                    <p class="text-xs font-medium text-slate-300">+2,000 pessoas já iniciaram a jornada</p>
                </div>
            </div>
        </div>

        {{-- Formulário de Registo (Direita) --}}
        <div class="w-full lg:w-1/2 p-8 md:p-12 lg:p-16 relative" 
             x-data="{
                email: '{{ old('email') }}',
                password: '',
                get isEmailValid() {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
                },
                get passwordStrength() {
                    let score = 0;
                    if (this.password.length > 7) score++;
                    if (/[A-Z]/.test(this.password)) score++;
                    if (/[0-9]/.test(this.password)) score++;
                    if (/[^A-Za-z0-9]/.test(this.password)) score++;
                    return score;
                }
             }">
             
            <div class="max-w-sm mx-auto">
                <div class="text-center lg:text-left mb-8">
                    <h1 class="text-3xl font-black text-slate-800 mb-2">Começar jornada.</h1>
                    <p class="text-slate-500 text-sm">
                        Já tens uma conta? <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-1">Entrar aqui</a>
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Como gostarias de ser chamado(a)?</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-user-smile-line text-slate-400"></i>
                            </div>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400"
                                   placeholder="O teu nome ou apelido">
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-xs text-rose-500" />
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">O teu Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-mail-line text-slate-400"></i>
                            </div>
                            <input id="email" type="email" name="email" x-model="email" required
                                   class="pl-10 pr-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400"
                                   placeholder="ola@exemplo.com">
                            
                            {{-- Feedback Visual de Email --}}
                            <div x-show="isEmailValid" x-transition.opacity class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="ri-check-line text-emerald-500 text-lg"></i>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500" />
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cria uma Password segura</label>
                        <div class="relative mb-2">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-lock-2-line text-slate-400"></i>
                            </div>
                            <input id="password" type="password" name="password" x-model="password" required
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400"
                                   placeholder="••••••••">
                        </div>
                        
                        {{-- Barra de Força da Password --}}
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden flex" x-show="password.length > 0" x-transition.opacity>
                            <div class="h-full transition-all duration-300"
                                 :class="{
                                     'w-1/4 bg-rose-500': passwordStrength === 1,
                                     'w-2/4 bg-amber-400': passwordStrength === 2,
                                     'w-3/4 bg-emerald-400': passwordStrength === 3,
                                     'w-full bg-emerald-500': passwordStrength === 4
                                 }"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 flex justify-between" x-show="password.length > 0">
                            <span x-text="passwordStrength < 3 ? 'Precisa de mais força' : 'Password excelente!'"></span>
                            <span x-show="passwordStrength < 3">Usa letras, números e símbolos</span>
                        </p>

                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500" />
                    </div>

                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-lock-check-line text-slate-400"></i>
                            </div>
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400"
                                   placeholder="Repete a password para confirmar">
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-500" />
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 min-h-[44px] rounded-xl bg-gradient-to-r from-indigo-600 to-violet-500 hover:from-indigo-700 hover:to-violet-600 text-white font-bold shadow-lg shadow-indigo-500/20 transform hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-indigo-500/50">
                        Criar a minha conta <i class="ri-arrow-right-line"></i>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
                        <i class="ri-shield-check-fill text-teal-500 text-lg"></i>
                        A tua identidade é mantida estritamente privada.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Link de emergência Global (Acessibilidade mantida) --}}
    <div class="fixed bottom-4 left-0 right-0 text-center z-20">
        <p class="text-xs text-slate-400">
            Em momento de crise aguda?
            <a href="tel:112" class="text-rose-500 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-rose-500 rounded px-1">Liga 112</a>
        </p>
    </div>

</body>
</html>