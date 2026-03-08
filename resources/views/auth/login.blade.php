<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | Lumina</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-gradient {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,0.05) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.05) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.05) 0, transparent 50%);
        }
    </style>
</head>
<body class="mesh-gradient min-h-screen flex items-center justify-center p-6 selection:bg-indigo-500/30">

    <div class="absolute top-6 left-6 z-20">
        <a href="{{ route('home') }}" class="flex items-center justify-center w-11 h-11 bg-white/50 backdrop-blur-sm rounded-full text-slate-500 hover:text-indigo-600 hover:bg-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
    </div>

    <div class="w-full max-w-md animate-fade-up">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-2xl shadow-xl shadow-indigo-900/5 flex items-center justify-center mx-auto mb-6 border border-slate-100">
                <i class="ri-leaf-fill text-3xl text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-800 mb-2">Bem-vindo(a) de volta.</h1>
            <p class="text-slate-500 text-sm">
                Ainda não tens conta? <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-1">Criar agora</a>
            </p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl border border-white/50">
            
            <x-auth-session-status class="mb-6 text-sm text-emerald-600 font-bold bg-emerald-50 p-4 rounded-xl text-center" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">O teu Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ri-mail-line text-slate-400"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400"
                               placeholder="ola@exemplo.com">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500" />
                </div>

                <div x-data="{ showPassword: false }">
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-widest">A tua Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-1">
                                Esqueceste-te?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ri-lock-2-line text-slate-400"></i>
                        </div>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                               class="pl-10 pr-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400" 
                               placeholder="••••••••">
                        
                        {{-- Botão de Mostrar/Ocultar --}}
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 min-h-[44px] min-w-[44px] justify-center focus:outline-none">
                            <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'" class="text-lg"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500" />
                </div>

                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="remember_me" class="ml-2 block text-sm font-medium text-slate-600 cursor-pointer min-h-[44px] flex items-center">Manter sessão iniciada</label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 min-h-[44px] rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-500 hover:from-indigo-700 hover:to-cyan-600 text-white font-bold shadow-lg shadow-indigo-500/20 transform hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-indigo-500/50">
                    Entrar na Lumina <i class="ri-arrow-right-line"></i>
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <p class="text-xs text-slate-400">
                A precisar de ajuda imediata? <a href="tel:112" class="text-rose-500 font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-rose-500 rounded px-1">Ligar Linha de Crise</a>
            </p>
        </div>
    </div>

</body>
</html>