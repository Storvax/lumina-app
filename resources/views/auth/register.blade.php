<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registo | Lumina</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .mesh-gradient {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,0.05) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.05) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.05) 0, transparent 50%);
            background-image: 
                radial-gradient(at 40% 20%, hsla(260,100%,94%,1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(189,100%,92%,1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(341,100%,96%,1) 0px, transparent 50%);
        }
    </style>
</head>
<body class="antialiased font-sans text-slate-600 bg-slate-50 h-screen w-full overflow-hidden relative selection:bg-indigo-500 selection:text-white">

    <div class="absolute inset-0 mesh-gradient -z-10"></div>
    
    <div class="absolute top-10 right-10 w-64 h-64 bg-teal-200/30 rounded-full blur-3xl animate-[float_8s_ease-in-out_infinite]"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl animate-[float_10s_ease-in-out_infinite]"></div>

    <a href="{{ url('/') }}" class="absolute top-6 left-6 z-20 flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors bg-white/50 px-4 py-2 rounded-full backdrop-blur-sm border border-white/50">
        <i class="ri-arrow-left-line"></i> Voltar à casa
    </a>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 overflow-hidden grid md:grid-cols-2 relative z-10">
            
            <div class="p-8 md:p-12 flex flex-col justify-center order-2 md:order-1">
                <div class="text-center md:text-left mb-6">
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Começa a tua jornada</h1>
                    <p class="text-slate-500 text-sm">Já tens conta? <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Faz login aqui</a>.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-sm">
                        <div class="font-bold mb-1"><i class="ri-error-warning-line"></i> Atenção:</div>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nickname (Anónimo)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-user-smile-line"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400" placeholder="Escolhe um nome seguro...">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 ml-1">Este é o nome que os outros vão ver. Evita usar o teu nome real completo.</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-mail-line"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400" placeholder="exemplo@email.com">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-lock-2-line"></i>
                            </div>
                            <input type="password" name="password" id="password" required class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400" placeholder="••••••••">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirmar Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="ri-lock-check-line"></i>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 text-sm placeholder:text-slate-400" placeholder="Repete a password">
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" type="checkbox" required class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        </div>
                        <div class="ml-2 text-xs text-slate-500">
                            Aceito os <a href="#" class="underline hover:text-indigo-600">Termos e Condições</a> e compreendo que a Lumina é uma comunidade de pares, não um serviço de emergência médica.
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white font-bold shadow-lg shadow-teal-500/20 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        Criar Conta <i class="ri-user-add-line"></i>
                    </button>
                </form>
            </div>

            <div class="hidden md:flex flex-col justify-between p-12 bg-teal-600 relative overflow-hidden text-white order-1 md:order-2">
                <img src="{{ asset('images/register.jpg') }}" class="absolute inset-0 w-full h-full object-cover opacity-30 mix-blend-overlay" alt="Natureza">                

                <div class="relative z-10 text-right">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur-sm mb-6 ml-auto">
                        <span class="font-bold text-xl">L</span>
                    </div>
                    <h2 class="text-3xl font-bold leading-tight">Um novo capítulo começa hoje.</h2>
                </div>

                <div class="relative z-10 bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 mt-auto">
                    <p class="text-teal-50 italic text-lg mb-4">"Não precisas de ver a escada toda. Apenas dá o primeiro passo."</p>
                    <div class="flex items-center justify-end gap-2">
                        <p class="text-xs text-white opacity-80">- Martin Luther King Jr.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>