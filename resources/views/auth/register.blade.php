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
                radial-gradient(at 0% 50%, hsla(341,100%,96%,1) 0px, transparent 50%);
        }
    </style>
</head>
<body class="antialiased text-slate-600 bg-slate-50 min-h-screen w-full relative selection:bg-indigo-500 selection:text-white">

    <div class="fixed inset-0 mesh-gradient -z-10 pointer-events-none"></div>
    <div class="fixed top-10 left-10 w-64 h-64 bg-indigo-200/30 rounded-full blur-3xl -z-10 pointer-events-none"></div>
    <div class="fixed bottom-10 right-10 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl -z-10 pointer-events-none"></div>

    {{-- Botão voltar --}}
    <a href="{{ url('/') }}" class="fixed top-6 left-6 z-20 flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors bg-white/50 px-4 py-2 rounded-full backdrop-blur-sm border border-white/50">
        <i class="ri-arrow-left-line"></i> Voltar
    </a>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 overflow-hidden grid md:grid-cols-2 relative z-10">

            {{-- Painel esquerdo — Visual / Motivacional --}}
            <div class="hidden md:flex flex-col justify-between p-12 bg-gradient-to-br from-indigo-600 to-violet-600 relative overflow-hidden text-white">
                <img src="https://images.unsplash.com/photo-1499209974431-9dddcece7f88?q=80&w=1000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-overlay" alt="Bem-estar">

                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur-sm mb-6">
                        <span class="font-bold text-xl">L</span>
                    </div>
                    <h2 class="text-3xl font-bold leading-tight">O teu espaço seguro<br>começa aqui.</h2>
                    <p class="text-indigo-200 mt-3 text-sm leading-relaxed">Um lugar onde podes ser tu, sem filtros e sem julgamentos. Vamos construir o teu refúgio juntos.</p>
                </div>

                <div class="relative z-10 space-y-4">
                    <div class="bg-white/10 backdrop-blur-md p-5 rounded-2xl border border-white/10">
                        <p class="text-indigo-100 italic text-lg mb-4">"A coragem de pedir ajuda é o primeiro passo para a cura."</p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-400 border border-white/30 flex items-center justify-center text-sm">
                                <i class="ri-heart-pulse-fill"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider opacity-70">Comunidade Lumina</p>
                                <p class="text-xs text-white/80">Espaço seguro e anónimo</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10 text-xs">
                            <i class="ri-shield-check-fill text-teal-300"></i>
                            <span class="text-white/80">100% Anónimo</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10 text-xs">
                            <i class="ri-lock-fill text-teal-300"></i>
                            <span class="text-white/80">Dados encriptados</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Painel direito — Formulário --}}
            <div class="p-8 md:p-12 flex flex-col justify-center">

                {{-- Logo mobile --}}
                <div class="md:hidden text-center mb-6">
                    <div class="inline-flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-400 flex items-center justify-center text-white font-bold text-xl shadow-md">L</div>
                        <span class="text-2xl font-bold text-slate-800 tracking-tight">Lumina<span class="text-indigo-500">.</span></span>
                    </div>
                </div>

                <div class="text-center md:text-left mb-8">
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Criar a tua conta</h1>
                    <p class="text-slate-500 text-sm">Já tens conta? <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Entra aqui</a>.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-sm">
                        <div class="font-bold mb-1"><i class="ri-error-warning-line"></i> Oops! Algo correu mal.</div>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    {{-- Nome --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Como gostas de ser chamado(a)?</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-user-smile-line"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400"
                                   placeholder="O teu nome ou nickname">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-mail-line"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400"
                                   placeholder="exemplo@email.com">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-lock-2-line"></i>
                            </div>
                            <input type="password" name="password" id="password" required
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400"
                                   placeholder="Mínimo 8 caracteres">
                        </div>
                    </div>

                    {{-- Confirmar Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirmar Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-lock-check-line"></i>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400"
                                   placeholder="Repete a password">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-500 hover:from-indigo-700 hover:to-violet-600 text-white font-bold shadow-lg shadow-indigo-500/20 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        Criar a minha conta <i class="ri-arrow-right-line"></i>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
                        <i class="ri-shield-check-line text-teal-500"></i>
                        A tua identidade está protegida e encriptada.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Link de emergência --}}
    <div class="fixed bottom-4 left-0 right-0 text-center z-20">
        <p class="text-xs text-slate-400">
            Em emergência?
            <a href="tel:112" class="text-rose-500 font-bold hover:underline">Liga o 112</a>
            ou
            <a href="tel:808242424" class="text-blue-500 font-bold hover:underline">SNS 24</a>
        </p>
    </div>

</body>
</html>
