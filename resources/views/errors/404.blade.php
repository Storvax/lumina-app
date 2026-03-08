<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página não encontrada | Lumina</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full text-center animate-fade-up">
        <div class="w-24 h-24 bg-white rounded-[2rem] shadow-xl shadow-indigo-900/5 flex items-center justify-center mx-auto mb-8 border border-slate-100 transform -rotate-6">
            <i class="ri-map-pin-line text-4xl text-indigo-300"></i>
        </div>
        
        <h1 class="text-3xl font-black text-slate-800 mb-3">Perdemos o rasto.</h1>
        <p class="text-slate-500 leading-relaxed mb-8">
            Parece que tentaste seguir por um trilho que não existe ou que foi movido. Não te preocupes, o teu espaço seguro continua intacto.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('dashboard') }}" class="w-full py-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                <i class="ri-home-smile-line"></i> Voltar ao Início
            </a>
            <a href="{{ route('calm.index') }}" class="w-full py-4 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold transition-all flex items-center justify-center gap-2">
                <i class="ri-leaf-line text-emerald-500"></i> Ir para a Zona Calma
            </a>
        </div>
    </div>

</body>
</html>