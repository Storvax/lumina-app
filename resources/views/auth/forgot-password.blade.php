<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Password | Lumina</title>

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
        <a href="{{ route('login') }}" class="flex items-center justify-center w-11 h-11 bg-white/50 backdrop-blur-sm rounded-full text-slate-500 hover:text-indigo-600 hover:bg-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
    </div>

    <div class="w-full max-w-md animate-fade-up">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-2xl shadow-xl shadow-indigo-900/5 flex items-center justify-center mx-auto mb-6 border border-slate-100">
                <i class="ri-lock-unlock-line text-3xl text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-800 mb-2">Perdeste a chave?</h1>
            <p class="text-slate-500 text-sm max-w-xs mx-auto leading-relaxed">
                Acontece a todos. Partilha o teu email e vamos enviar-te um link seguro para voltares a entrar no teu espaço.
            </p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-2xl border border-white/50"
             x-data="{ 
                 isSubmitting: false, 
                 cooldown: 0,
                 startCooldown() {
                     this.isSubmitting = true;
                     // Não bloqueia o submit real do formulário, apenas gere a UI
                     setTimeout(() => {
                         this.cooldown = 60;
                         let timer = setInterval(() => {
                             this.cooldown--;
                             if(this.cooldown <= 0) {
                                 clearInterval(timer);
                                 this.isSubmitting = false;
                             }
                         }, 1000);
                     }, 1000); // Dá 1 segundo para submeter o pedido
                 }
             }">
            
            <x-auth-session-status class="mb-6 text-sm text-emerald-600 font-bold bg-emerald-50 p-4 rounded-xl text-center border border-emerald-100" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" @submit="startCooldown" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">O teu Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ri-mail-line text-slate-400"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all py-3 min-h-[44px] text-sm placeholder:text-slate-400"
                               placeholder="ola@exemplo.com">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500" />
                </div>

                <button type="submit" :disabled="isSubmitting || cooldown > 0" 
                        class="w-full py-3.5 px-4 min-h-[44px] rounded-xl bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:hover:bg-slate-900 text-white font-bold shadow-lg shadow-slate-900/20 transform hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-slate-500/50">
                    
                    <span x-show="!isSubmitting && cooldown === 0">Enviar Link de Recuperação</span>
                    <span x-show="isSubmitting && cooldown === 0"><i class="ri-loader-4-line animate-spin"></i> A preparar envio...</span>
                    <span x-show="cooldown > 0" x-text="`Aguarda ${cooldown}s para pedir novamente`" x-cloak></span>
                    
                </button>
            </form>
        </div>
    </div>

</body>
</html>