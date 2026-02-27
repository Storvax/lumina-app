<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Reverb/WebSocket config (público — o que o browser usa para ligar ao WebSocket) -->
        <script>
            window.reverbConfig = {
                key: "{{ config('reverb.apps.apps.0.key') }}",
                host: "{{ config('reverb.apps.apps.0.options.host') }}",
                port: {{ (int) config('reverb.apps.apps.0.options.port', 443) }},
                scheme: "{{ config('reverb.apps.apps.0.options.scheme', 'https') }}"
            };
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @if(session('gamification.flames') || session('gamification.badge') || session('gamification.mission'))
            <div id="gamification-toast" class="fixed bottom-6 right-6 z-50 animate-fade-up">
                <div class="bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl border border-slate-700 flex items-center gap-4">
                    
                    @if(session('gamification.badge'))
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center text-yellow-400 text-2xl">
                            <i class="{{ session('gamification.badge')['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-yellow-500 uppercase">Badge Desbloqueado!</p>
                            <p class="font-bold text-lg">{{ session('gamification.badge')['name'] }}</p>
                        </div>
                        
                    @elseif(session('gamification.mission'))
                        <div class="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center text-orange-500 text-2xl">
                            <i class="ri-focus-2-line"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-orange-400 uppercase">Missão Concluída!</p>
                            <p class="font-bold text-sm">{{ session('gamification.mission') }}</p>
                            <p class="text-xs text-slate-400">Griste as tuas chamas!</p>
                        </div>
                        
                    @elseif(session('gamification.flames'))
                        <div class="w-10 h-10 bg-orange-500/20 rounded-full flex items-center justify-center text-orange-500 text-xl">
                            <i class="ri-fire-fill"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg">+{{ session('gamification.flames') }} Chamas</p>
                            <p class="text-xs text-slate-400">Continua assim!</p>
                        </div>
                    @endif
            
                </div>
            </div>
            
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('gamification-toast');
                    if(toast) {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateY(20px)';
                        toast.style.transition = 'all 0.5s ease';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 4000);
            </script>
            @endif
    </body>
</html>
