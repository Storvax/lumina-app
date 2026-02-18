<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lumina | O teu espaço seguro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 font-sans selection:bg-indigo-500 selection:text-white relative transition-colors duration-300">

    @include('landing.modals')

    @include('landing.nav')

    @include('landing.hero')

    @include('landing.pulse')
    
    @include('landing.features') @include('landing.calm') @include('landing.community') @include('landing.forum') @include('landing.library') @include('landing.articles') @include('landing.cta') @include('landing.footer')

    @include('landing.scripts')

</body>
</html>