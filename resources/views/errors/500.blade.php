<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Problema Técnico | Lumina</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-rose-50/50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full text-center animate-fade-up">
        <div class="w-24 h-24 bg-white rounded-[2rem] shadow-xl shadow-rose-900/5 flex items-center justify-center mx-auto mb-8 border border-rose-100 animate-pulse">
            <i class="ri-server-line text-4xl text-rose-400"></i>
        </div>
        
        <h1 class="text-3xl font-black text-slate-800 mb-3">Precisamos de respirar.</h1>
        <p class="text-slate-600 leading-relaxed mb-8">
            A nossa plataforma está a passar por um momento de sobrecarga técnica. A nossa equipa já foi notificada. Tenta recarregar a página daqui a pouco.
        </p>

        <button onclick="window.location.reload()" class="w-full py-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 mb-4">
            <i class="ri-refresh-line"></i> Tentar novamente
        </button>
        
        <p class="text-xs text-slate-400">
            Se precisares de ajuda urgente, <a href="tel:112" class="text-rose-500 font-bold hover:underline">liga 112</a>.
        </p>
    </div>

</body>
</html>