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
<body class="antialiased text-slate-600 bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white relative">

    <div id="sosModal" class="fixed inset-0 z-[100] hidden">
        <div id="modalOverlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity cursor-pointer"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4 animate-fade-up">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-rose-100">
                <div class="bg-rose-50 p-6 text-center border-b border-rose-100">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-500 text-3xl">
                        <i class="ri-alarm-warning-fill"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">Ajuda Imediata</h3>
                    <p class="text-slate-600 mt-2 text-sm">Não estás sozinho. Estas linhas estão disponíveis agora.</p>
                </div>
                <div class="p-6 space-y-4">
                    <a href="tel:112" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-rose-50 hover:border-rose-200 transition-colors group">
                        <div class="flex items-center gap-4">
                            <span class="text-2xl font-black text-slate-800 group-hover:text-rose-600">112</span>
                            <div class="text-left">
                                <p class="font-bold text-slate-800">Emergência Nacional</p>
                                <p class="text-xs text-slate-500">Risco de vida iminente</p>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-rose-500 shadow-sm">
                            <i class="ri-phone-fill"></i>
                        </div>
                    </a>
                    <a href="tel:808242424" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-50 hover:border-blue-200 transition-colors group">
                        <div class="flex items-center gap-4">
                            <span class="text-xl font-bold text-slate-800 group-hover:text-blue-600">SNS 24</span>
                            <div class="text-left">
                                <p class="font-bold text-slate-800">Apoio Psicológico</p>
                                <p class="text-xs text-slate-500">Disponível 24h por dia</p>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-500 shadow-sm">
                            <i class="ri-phone-fill"></i>
                        </div>
                    </a>
                </div>
                <div class="bg-slate-50 p-4 text-center">
                    <button id="modalClose" class="text-slate-500 font-semibold hover:text-slate-800 text-sm">Cancelar / Voltar ao site</button>
                </div>
            </div>
        </div>
    </div>

    <a href="https://www.google.pt" class="fixed bottom-6 right-6 z-[60] bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full shadow-xl flex items-center gap-2 transition-transform hover:scale-105 border-4 border-white ring-2 ring-red-100" title="Sair rapidamente para o Google">
        <i class="ri-eye-off-line text-xl"></i> 
        <span class="hidden md:inline">Saída Rápida</span>
    </a>

    <nav class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="glass max-w-6xl mx-auto mt-4 md:rounded-full rounded-2xl px-6 py-3 flex justify-between items-center shadow-lg shadow-black/5 mx-4 md:mx-auto">
            <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-primary-500 to-indigo-400 flex items-center justify-center text-white font-bold text-lg group-hover:rotate-12 transition-transform">L</div>
                <span class="text-xl font-bold text-slate-800 tracking-tight">Lumina<span class="text-primary-500">.</span></span>
            </a>

            <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="#inicio" class="text-slate-600 hover:text-primary-600 transition-colors">Início</a>
                <a href="#calma" class="text-slate-600 hover:text-primary-600 transition-colors">Zona Calma</a>
                <a href="#comunidade" class="text-slate-600 hover:text-primary-600 transition-colors">Comunidade</a>
                <a href="#forum" class="text-slate-600 hover:text-primary-600 transition-colors">Fórum</a>
                <a href="#biblioteca" class="text-slate-600 hover:text-primary-600 transition-colors">Biblioteca</a>
            </div>

            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hidden md:flex text-sm font-semibold text-primary-600 hover:bg-primary-50 px-4 py-2 rounded-full transition-colors">
                            Minha Conta
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden md:flex text-sm font-semibold text-primary-600 hover:bg-primary-50 px-4 py-2 rounded-full transition-colors">
                            Login Anónimo
                        </a>
                    @endauth
                @endif

                <button class="bg-white border border-rose-100 text-rose-500 hover:bg-rose-50 hover:border-rose-200 px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                    </span>
                    SOS
                </button>
                
                <button id="mobileMenuBtn" class="md:hidden text-slate-600 p-2 focus:outline-none">
                    <i class="ri-menu-line text-2xl"></i>
                </button>
            </div>
        </div>

        <div id="mobileMenu" class="hidden absolute top-20 left-4 right-4 bg-white rounded-3xl shadow-xl border border-slate-100 p-6 flex flex-col gap-4 animate-fade-up md:hidden">
            <a href="#inicio" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Início</a>
            <a href="#calma" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Zona Calma</a>
            <a href="#comunidade" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Comunidade</a>
            <a href="#forum" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Fórum</a>
            <a href="#biblioteca" class="mobile-link text-lg font-medium text-slate-600 hover:text-primary-600">Biblioteca</a>
            <hr class="border-slate-100">
            @auth
                <a href="{{ url('/dashboard') }}" class="text-center w-full py-3 rounded-xl bg-primary-50 text-primary-600 font-bold">Minha Conta</a>
            @else
                <a href="{{ route('login') }}" class="text-center w-full py-3 rounded-xl bg-primary-50 text-primary-600 font-bold">Login Anónimo</a>
            @endauth
        </div>
    </nav>

    <section id="inicio" class="relative pt-40 pb-24 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full mesh-gradient opacity-60 -z-10"></div>
        
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8 animate-fade-up">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/60 border border-white text-primary-600 text-xs font-bold uppercase tracking-wider shadow-sm backdrop-blur-sm">
                    🚀 Versão Beta Disponível
                </div>
                
                <h1 class="text-5xl md:text-6xl/tight font-extrabold text-slate-900 tracking-tight text-balance">
                    Não tens de carregar o mundo <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-500 to-indigo-600">sozinho.</span>
                </h1>
                
                <p class="text-lg md:text-xl text-slate-500 font-light leading-relaxed max-w-lg">
                    Um porto seguro digital. Entra em salas anónimas, desabafa com ouvintes treinados ou usa o nosso diário inteligente. Sem julgamentos.
                </p>

                <div class="bg-white/40 p-1 rounded-2xl border border-white/50 inline-block backdrop-blur-sm">
                    <div class="glass-card p-5 rounded-xl flex flex-col gap-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Como te sentes hoje?</span>
                        <div class="flex gap-2 sm:gap-3 justify-center flex-wrap sm:flex-nowrap">
                            
                            <a href="#comunidade" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-amber-200 transition-all duration-300">
                                <i class="ri-thunderstorms-line text-2xl text-slate-400 group-hover:text-amber-500 transition-colors mb-1 sm:mb-0"></i>
                                <span class="text-[10px] font-bold text-amber-500 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Ansioso</span>
                            </a>
                            
                            <a href="#comunidade" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-slate-400 transition-all duration-300">
                                <i class="ri-cloud-off-line text-2xl text-slate-400 group-hover:text-slate-600 transition-colors mb-1 sm:mb-0"></i>
                                <span class="text-[10px] font-bold text-slate-600 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Triste</span>
                            </a>

                            <a href="#comunidade" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-rose-300 transition-all duration-300">
                                <i class="ri-fire-line text-2xl text-slate-400 group-hover:text-rose-500 transition-colors mb-1 sm:mb-0"></i>
                                <span class="text-[10px] font-bold text-rose-500 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Irritado</span>
                            </a>
                            
                            <a href="#comunidade" class="group relative flex flex-col items-center justify-center w-20 h-20 sm:w-14 sm:h-14 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-teal-300 transition-all duration-300">
                                <i class="ri-sun-line text-2xl text-slate-400 group-hover:text-teal-500 transition-colors mb-1 sm:mb-0"></i>
                                <span class="text-[10px] font-bold text-teal-500 lg:absolute lg:-bottom-6 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">Bem</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <a href="#comunidade" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-primary-500 to-indigo-500 text-white font-semibold shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/40 hover:-translate-y-0.5 transition-all">
                        Entrar na Comunidade
                    </a>
                    <a href="#calma" class="px-6 py-4 rounded-2xl text-slate-600 font-medium hover:bg-white/50 transition-colors">
                        <i class="ri-play-circle-line align-bottom text-xl mr-1"></i> Ver vídeo
                    </a>
                </div>
            </div>

            <div class="relative hidden lg:block animate-float">
                <div class="absolute -top-10 -right-10 w-72 h-72 bg-purple-200/50 rounded-full blur-3xl mix-blend-multiply"></div>
                <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-teal-200/50 rounded-full blur-3xl mix-blend-multiply"></div>
                
                <div class="glass-card p-6 rounded-3xl relative z-10 w-[85%] mx-auto rotate-[-2deg]">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-emerald-400 flex items-center justify-center text-white text-xs font-bold">PT</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Sala: Ansiedade Social</p>
                            <p class="text-xs text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> 24 Online</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="bg-slate-50 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 shadow-sm max-w-[80%]">
                                Alguém acordado? Sinto-me super ansioso com a reunião de amanhã... 😰
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <div class="bg-primary-50 p-3 rounded-2xl rounded-tr-none text-sm text-primary-800 shadow-sm max-w-[80%]">
                                Estou aqui. Respira fundo. O que é que te preocupa mais?
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="bg-slate-50 p-3 rounded-2xl rounded-tl-none text-sm text-slate-600 shadow-sm max-w-[80%]">
                                Medo de bloquear quando for a minha vez de falar.
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 relative">
                        <div class="w-full h-10 bg-slate-100 rounded-full"></div>
                        <div class="absolute right-1 top-1 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm"><i class="ri-send-plane-fill"></i></div>
                    </div>
                </div>

                <div class="glass-card absolute -bottom-6 -left-4 p-4 rounded-2xl flex items-center gap-3 animate-[float_4s_ease-in-out_infinite_1s]">
                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center"><i class="ri-heart-pulse-fill"></i></div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Vidas tocadas</p>
                        <p class="text-lg font-bold text-slate-800">+1,240</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-primary-500 font-bold text-sm tracking-widest uppercase">Começa a tua jornada</span>
                <h2 class="text-3xl font-bold text-slate-900 mt-2">Como usar a Lumina em 3 passos</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8 relative">
                <div class="hidden md:block absolute top-12 left-1/6 right-1/6 h-0.5 bg-slate-100 -z-10"></div>
                <div class="text-center">
                    <div class="w-24 h-24 mx-auto bg-white rounded-full border-4 border-slate-50 flex items-center justify-center mb-6 shadow-sm relative z-10">
                        <span class="text-4xl font-bold text-primary-200">1</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Entra Anónimo</h3>
                    <p class="text-slate-500 text-sm px-8">Cria um "nickname" e escolhe um avatar. Não pedimos nome real, nem foto, nem morada.</p>
                </div>
                <div class="text-center">
                    <div class="w-24 h-24 mx-auto bg-white rounded-full border-4 border-slate-50 flex items-center justify-center mb-6 shadow-sm relative z-10">
                        <span class="text-4xl font-bold text-primary-400">2</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Escolhe a tua Sala</h3>
                    <p class="text-slate-500 text-sm px-8">Navega pelos temas. Se estás triste, entra na sala de Apoio. Se queres rir, vai para o "Off-Topic".</p>
                </div>
                <div class="text-center">
                    <div class="w-24 h-24 mx-auto bg-white rounded-full border-4 border-slate-50 flex items-center justify-center mb-6 shadow-sm relative z-10">
                        <span class="text-4xl font-bold text-primary-600">3</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Impulsiona a Mudança</h3>
                    <p class="text-slate-500 text-sm px-8">Quando te sentires pronto, usa as nossas ferramentas para encontrar um psicólogo compatível.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="calma" class="py-24 bg-calm-50 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-white/40 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-teal-200/20 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-2">Zona de Calma Imediata</h2>
                <p class="text-slate-500">Ferramentas para usares agora, se estiveres a sentir-te sobrecarregado.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl shadow-sm text-center border border-white">
                    <h3 class="font-bold text-slate-800 mb-8"><i class="ri-lungs-line text-teal-500 mr-2"></i> Respiração Guiada</h3>
                    
                    <div class="relative w-48 h-48 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 bg-teal-200/50 rounded-full animate-breathe blur-xl"></div>
                        <div class="absolute inset-4 bg-teal-300/50 rounded-full animate-breathe blur-md"></div>
                        <div class="relative z-10 w-32 h-32 bg-teal-500 rounded-full flex items-center justify-center shadow-lg animate-breathe text-white font-bold tracking-widest text-sm">
                            RESPIRA
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mt-8">Segue o ritmo da bola. Inspira quando cresce, expira quando diminui.</p>
                </div>

                <div class="space-y-6">
                    <h3 class="font-bold text-slate-800"><i class="ri-headphone-line text-primary-500 mr-2"></i> Sons de Portugal</h3>
                    <div class="grid gap-4">
                        <button class="sound-btn flex items-center justify-between p-4 bg-white hover:bg-primary-50 rounded-2xl border border-slate-100 transition-all group" data-sound-name="Chuva na Serra da Estrela" data-sound="{{ asset('sounds/chuva.mp3') }}">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center"><i class="ri-drop-line"></i></div>
                                <div class="text-left">
                                    <h4 class="font-bold text-slate-700 group-hover:text-primary-600">Chuva na Serra da Estrela</h4>
                                    <p class="text-xs text-slate-400">Som contínuo • 30 min</p>
                                </div>
                            </div>
                            <i class="play-icon ri-play-circle-fill text-3xl text-slate-200 group-hover:text-primary-500 transition-colors"></i>
                        </button>

                        <button class="sound-btn flex items-center justify-between p-4 bg-white hover:bg-teal-50 rounded-2xl border border-slate-100 transition-all group" data-sound-name="Ondas na Nazaré" data-sound="{{ asset('sounds/mar.mp3') }}">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center"><i class="ri-sailboat-line"></i></div>
                                <div class="text-left">
                                    <h4 class="font-bold text-slate-700 group-hover:text-teal-600">Ondas na Nazaré</h4>
                                    <p class="text-xs text-slate-400">Mar calmo • 45 min</p>
                                </div>
                            </div>
                            <i class="play-icon ri-play-circle-fill text-3xl text-slate-200 group-hover:text-teal-500 transition-colors"></i>
                        </button>

                         <button class="sound-btn flex items-center justify-between p-4 bg-white hover:bg-amber-50 rounded-2xl border border-slate-100 transition-all group" data-sound-name="Lareira Alentejana" data-sound="{{ asset('sounds/fogo.mp3') }}">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center"><i class="ri-fire-line"></i></div>
                                <div class="text-left">
                                    <h4 class="font-bold text-slate-700 group-hover:text-amber-600">Lareira Alentejana</h4>
                                    <p class="text-xs text-slate-400">Crepitar do lume • 1h</p>
                                </div>
                            </div>
                            <i class="play-icon ri-play-circle-fill text-3xl text-slate-200 group-hover:text-amber-500 transition-colors"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="comunidade" class="py-24 bg-white/50 backdrop-blur-sm relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">O teu kit de ferramentas emocionais</h2>
                <p class="text-slate-500">Escolhe o que precisas hoje. Tudo desenhado para ser privado e seguro.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="group relative bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-50 rounded-bl-[100px] -mr-8 -mt-8 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-primary-100 text-primary-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                            <i class="ri-group-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">A Fogueira</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-6">Salas de áudio e texto temáticas. Entra na sala "Luto" ou "Ansiedade" e percebe que não estás só.</p>
                        <a href="#" class="inline-flex items-center text-sm font-bold text-primary-600 hover:text-primary-700">
                            Ver salas ativas <i class="ri-arrow-right-line ml-1 transition-transform group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>

                <div class="group relative bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-calm-50 rounded-bl-[100px] -mr-8 -mt-8 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-teal-100 text-teal-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                            <i class="ri-headphone-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">O Ouvinte</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-6">Às vezes só precisamos de ser ouvidos. Pede um "Buddy" treinado para um chat 1-para-1.</p>
                        <a href="#" class="inline-flex items-center text-sm font-bold text-teal-600 hover:text-teal-700">
                            Pedir conversa <i class="ri-arrow-right-line ml-1 transition-transform group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>

                <div class="group relative bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-[100px] -mr-8 -mt-8 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-indigo-100 text-indigo-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                            <i class="ri-book-open-line"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Diário IA</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-6">Regista o teu dia. A nossa IA deteta padrões de humor e alerta-te se precisares de ajuda extra.</p>
                        <a href="#" class="inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-700">
                            Abrir meu espaço <i class="ri-arrow-right-line ml-1 transition-transform group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="forum" class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Mural da Esperança</h2>
                    <p class="text-slate-500 max-w-xl">Discussões assíncronas. Deixa o teu pensamento, volta mais tarde para ver o apoio que recebeste.</p>
                </div>
                <a href="#" class="px-6 py-3 rounded-xl bg-primary-50 text-primary-600 font-bold hover:bg-primary-100 transition-colors">
                    <i class="ri-add-line mr-1"></i> Criar Tópico
                </a>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div class="group bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md hover:border-primary-100 transition-all cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold">A</div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-600">#Vitória</span>
                                        <span class="text-xs text-slate-400">há 2h</span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-primary-600 transition-colors">Consegui ir ao supermercado sozinho hoje!</h4>
                                    <p class="text-slate-500 text-sm mt-2 line-clamp-2">Parece estúpido, mas para quem tem agorafobia isto é gigante. Só queria partilhar com alguém que perceba...</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-1 text-slate-400">
                                <i class="ri-heart-line text-xl group-hover:text-rose-500 transition-colors"></i>
                                <span class="text-xs font-bold">24</span>
                            </div>
                        </div>
                    </div>

                    <div class="group bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md hover:border-primary-100 transition-all cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">R</div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-600">#Conselho</span>
                                        <span class="text-xs text-slate-400">há 5h</span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-primary-600 transition-colors">Como lidam com a ansiedade de domingo à noite?</h4>
                                    <p class="text-slate-500 text-sm mt-2 line-clamp-2">Todos os domingos o meu coração começa a bater mais rápido a pensar na segunda-feira...</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-1 text-slate-400">
                                <i class="ri-chat-1-line text-xl group-hover:text-primary-500 transition-colors"></i>
                                <span class="text-xs font-bold">12</span>
                            </div>
                        </div>
                    </div>
                    <button class="w-full py-3 text-center text-sm font-bold text-slate-500 hover:text-primary-600 hover:bg-slate-50 rounded-xl transition-all">Ver todos os tópicos</button>
                </div>

                <div class="bg-slate-50 rounded-3xl p-6 h-fit">
                    <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><i class="ri-hashtag"></i> O que se fala agora</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="#" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:border-primary-300 hover:text-primary-600 transition-all">Depressão</a>
                        <a href="#" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:border-primary-300 hover:text-primary-600 transition-all">Ansiedade Social</a>
                        <a href="#" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:border-primary-300 hover:text-primary-600 transition-all">Work-Life Balance</a>
                        <a href="#" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:border-primary-300 hover:text-primary-600 transition-all">Luto</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="biblioteca" class="py-24 bg-slate-50 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">A Nossa Biblioteca</h2>
                    <p class="text-slate-500">Livros, playlists e coisas que ajudaram a comunidade a sobreviver.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <div class="group bg-white p-4 rounded-2xl shadow-sm hover:shadow-lg transition-all border border-slate-100">
                    <div class="relative aspect-[2/3] bg-slate-200 rounded-xl mb-4 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=400&auto=format&fit=crop" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500" alt="Livro">
                        <div class="absolute top-2 right-2 bg-white/90 backdrop-blur rounded-lg p-1.5 shadow-sm">
                            <i class="ri-book-read-line text-indigo-500"></i>
                        </div>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm leading-tight mb-1">O Poder do Agora</h4>
                    <p class="text-xs text-slate-500 mb-3">Eckhart Tolle</p>
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-2">
                            <div class="w-6 h-6 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-[8px] font-bold text-blue-600">JP</div>
                            <div class="w-6 h-6 rounded-full bg-green-100 border-2 border-white flex items-center justify-center text-[8px] font-bold text-green-600">AN</div>
                        </div>
                        <span class="text-[10px] text-slate-400">+12 recomendam</span>
                    </div>
                </div>

                <div class="group bg-white p-4 rounded-2xl shadow-sm hover:shadow-lg transition-all border border-slate-100">
                    <div class="relative aspect-square bg-slate-200 rounded-xl mb-4 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1478737270239-2f02b77ac6d5?q=80&w=400&auto=format&fit=crop" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500" alt="Podcast">
                        <div class="absolute top-2 right-2 bg-white/90 backdrop-blur rounded-lg p-1.5 shadow-sm">
                            <i class="ri-mic-line text-rose-500"></i>
                        </div>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm leading-tight mb-1">Vozes da Mente</h4>
                    <p class="text-xs text-slate-500 mb-3">Podcast Spotify</p>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2 overflow-hidden">
                        <div class="bg-rose-400 h-full w-2/3"></div>
                    </div>
                    <span class="text-[10px] text-slate-400">Recomendado por Maria88</span>
                </div>

                 <div class="group bg-white p-4 rounded-2xl shadow-sm hover:shadow-lg transition-all border border-slate-100">
                    <div class="relative aspect-square bg-slate-200 rounded-xl mb-4 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=400&auto=format&fit=crop" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500" alt="Musica">
                        <div class="absolute top-2 right-2 bg-white/90 backdrop-blur rounded-lg p-1.5 shadow-sm">
                            <i class="ri-music-2-line text-teal-500"></i>
                        </div>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm leading-tight mb-1">Lo-Fi para Acalmar</h4>
                    <p class="text-xs text-slate-500 mb-3">Playlist Youtube</p>
                    <button class="w-full py-1.5 rounded-lg bg-teal-50 text-teal-600 text-xs font-bold hover:bg-teal-100 transition-colors">Ouvir Agora</button>
                </div>

                <div class="group bg-white border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center p-6 text-center hover:border-primary-300 hover:bg-primary-50/50 transition-all cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3 group-hover:bg-white text-slate-400 group-hover:text-primary-500 transition-colors">
                        <i class="ri-add-line text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-slate-700 text-sm">Adicionar Recurso</h4>
                    <p class="text-xs text-slate-400 mt-1">O que te ajudou a ti?</p>
                </div>
            </div>
        </div>
    </section>

    <section id="artigos" class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Artigos Profissionais</h2>
                    <p class="text-slate-500">Conteúdo verificado por psicólogos.</p>
                </div>
                <a href="#" class="hidden md:block text-primary-600 font-semibold hover:underline">Ver todos os artigos</a>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <a href="#" class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                    <div class="aspect-video bg-indigo-100 relative">
                         <img src="https://images.unsplash.com/photo-1499209974431-9dddcece7f88?q=80&w=600&auto=format&fit=crop" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500 opacity-90" alt="Calma">
                    </div>
                    <div class="p-4">
                        <span class="text-xs font-bold text-primary-500 uppercase">Ansiedade</span>
                        <h4 class="font-bold text-slate-800 mt-2 text-sm group-hover:text-primary-600 transition-colors">5 técnicas de respiração para parar um ataque de pânico.</h4>
                    </div>
                </a>

                <a href="#" class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                    <div class="aspect-video bg-teal-100 relative">
                        <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=600&auto=format&fit=crop" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500 opacity-90" alt="Amigos">
                    </div>
                    <div class="p-4">
                        <span class="text-xs font-bold text-teal-500 uppercase">Solidão</span>
                        <h4 class="font-bold text-slate-800 mt-2 text-sm group-hover:text-primary-600 transition-colors">Como fazer amigos em Portugal quando trabalhas remotamente.</h4>
                    </div>
                </a>

                <a href="#" class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                    <div class="aspect-video bg-rose-100 relative">
                        <img src="https://images.unsplash.com/photo-1515023115689-589c33041697?q=80&w=600&auto=format&fit=crop" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500 opacity-90" alt="Stress">
                    </div>
                    <div class="p-4">
                        <span class="text-xs font-bold text-rose-500 uppercase">Trabalho</span>
                        <h4 class="font-bold text-slate-800 mt-2 text-sm group-hover:text-primary-600 transition-colors">Burnout: Os sinais que o teu corpo te está a dar.</h4>
                    </div>
                </a>

                 <div class="bg-primary-50 rounded-2xl p-6 flex flex-col justify-center text-center border border-primary-100">
                    <i class="ri-mail-send-line text-3xl text-primary-500 mb-3"></i>
                    <h4 class="font-bold text-slate-800 mb-2">Dicas semanais?</h4>
                    <p class="text-xs text-slate-500 mb-4">Recebe exercícios de mindfulness no teu email.</p>
                    <input type="email" placeholder="Teu email..." class="w-full text-sm p-2 rounded-lg border-0 mb-2 focus:ring-2 ring-primary-300 focus:outline-none bg-white">
                    <button class="w-full bg-primary-600 text-white text-xs font-bold py-2 rounded-lg hover:bg-primary-700 transition-colors">Subscrever</button>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-slate-900 z-0">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary-500/20 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-teal-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6 tracking-tight">Sentes que falar já não chega?</h2>
            <p class="text-slate-300 text-lg mb-10 leading-relaxed">
                A comunidade é o primeiro passo, mas não substitui a terapia. Se sentes que precisas de ir mais fundo, faz um check-up emocional gratuito para perceberes se está na altura de falar com um profissional.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button class="bg-white text-slate-900 hover:bg-slate-50 font-bold py-4 px-8 rounded-full shadow-lg transition transform hover:scale-105 flex items-center justify-center">
                    <i class="ri-pulse-line text-xl mr-2 text-primary-600"></i>
                    Fazer Check-up Gratuito
                </button>
                <button class="bg-transparent border border-slate-700 text-white hover:bg-slate-800 font-semibold py-4 px-8 rounded-full transition flex items-center justify-center">
                    Ver lista de profissionais
                </button>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-100 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1 space-y-4">
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-lg">L</div>
                        <span class="text-xl font-bold text-slate-900">Lumina.</span>
                    </a>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Democratizar o acesso ao bem-estar mental em Portugal, criando pontes entre pessoas e profissionais.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"><i class="ri-instagram-line"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"><i class="ri-twitter-x-line"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"><i class="ri-linkedin-fill"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-slate-900 mb-6">Plataforma</h4>
                    <ul class="space-y-3 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-primary-600 transition-colors">A Fogueira (Salas)</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Ouvintes</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Para Psicólogos</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Planos para Empresas</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 mb-6">Legal</h4>
                    <ul class="space-y-3 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Termos de Uso</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Política de Privacidade</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Regras da Comunidade</a></li>
                        <li><a href="#" class="hover:text-primary-600 transition-colors">Livro de Reclamações</a></li>
                    </ul>
                </div>

                <div>
                    <div class="bg-amber-50 border border-amber-100 p-5 rounded-2xl">
                        <p class="text-xs font-bold text-amber-700 uppercase mb-2 flex items-center gap-1">
                            <i class="ri-alert-line"></i> Importante
                        </p>
                        <p class="text-xs text-amber-800/80 leading-relaxed">
                            A Lumina não presta atos médicos. Em caso de emergência ou risco de vida, liga imediatamente para o <span class="font-bold">112</span> ou <span class="font-bold">SNS24 (808 24 24 24)</span>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-400">© 2026 Lumina Portugal. Todos os direitos reservados.</p>
                <p class="text-xs text-slate-400 flex items-center gap-1">Feito com <i class="ri-heart-fill text-rose-400"></i> e empatia.</p>
            </div>
        </div>
    </footer>

    <div id="floatingPlayer" class="hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-50 bg-white/90 backdrop-blur-md rounded-full shadow-2xl border border-slate-100 px-6 py-3 flex items-center gap-6 animate-fade-up min-w-[300px]">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center animate-pulse">
                <i class="ri-music-fill"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">A tocar</span>
                <span id="playerTitle" class="text-sm font-bold text-slate-800">Som de Portugal</span>
            </div>
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <button id="playerControlBtn" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
                <i class="ri-pause-fill text-lg"></i>
            </button>
            <button id="playerCloseBtn" class="w-8 h-8 rounded-full bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-500 transition-colors">
                <i class="ri-stop-fill"></i>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- MODAL SOS ---
            const allButtons = document.querySelectorAll('button');
            const sosBtn = Array.from(allButtons).find(btn => btn.textContent.includes('SOS'));
            const modal = document.getElementById('sosModal');
            const overlay = document.getElementById('modalOverlay');
            const closeBtn = document.getElementById('modalClose');

            function toggleModal() {
                modal.classList.toggle('hidden');
            }
            if(sosBtn) sosBtn.addEventListener('click', toggleModal);
            if(overlay) overlay.addEventListener('click', toggleModal);
            if(closeBtn) closeBtn.addEventListener('click', toggleModal);

            // --- MENU MOBILE ---
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileLinks = document.querySelectorAll('.mobile-link');

            if(mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                    });
                });
            }

            // --- PLAYER DE SOM & BARRA FLUTUANTE ---
            let currentAudio = null;
            let currentBtn = null;

            const soundButtons = document.querySelectorAll('.sound-btn');
            const floatingPlayer = document.getElementById('floatingPlayer');
            const playerTitle = document.getElementById('playerTitle');
            const playerControlBtn = document.getElementById('playerControlBtn');
            const playerCloseBtn = document.getElementById('playerCloseBtn');

            // Função Auxiliar para atualizar ícones
            function updateIcons(state) {
                // state: 'play', 'pause', 'reset'
                if (!currentBtn) return;
                
                const cardIcon = currentBtn.querySelector('.play-icon');
                const floatIcon = playerControlBtn.querySelector('i');

                if (state === 'play') {
                    if(cardIcon) {
                        cardIcon.classList.remove('ri-play-circle-fill');
                        cardIcon.classList.add('ri-pause-circle-fill');
                    }
                    floatIcon.className = 'ri-pause-fill text-lg';
                } else if (state === 'pause') {
                    if(cardIcon) {
                        cardIcon.classList.remove('ri-pause-circle-fill');
                        cardIcon.classList.add('ri-play-circle-fill');
                    }
                    floatIcon.className = 'ri-play-fill text-lg';
                } else if (state === 'reset') {
                    if(cardIcon) {
                        cardIcon.classList.remove('ri-pause-circle-fill');
                        cardIcon.classList.add('ri-play-circle-fill');
                    }
                }
            }

            // Click nos cartões de som
            soundButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const soundPath = btn.getAttribute('data-sound');
                    const soundName = btn.getAttribute('data-sound-name');

                    // Se clicarmos no mesmo botão que já está a tocar
                    if (currentAudio && currentBtn === btn) {
                        if (currentAudio.paused) {
                            currentAudio.play();
                            updateIcons('play');
                        } else {
                            currentAudio.pause();
                            updateIcons('pause');
                        }
                        return;
                    }

                    // Se estiver a tocar outro, para o anterior
                    if (currentAudio) {
                        currentAudio.pause();
                        updateIcons('reset'); // Reseta o ícone do botão anterior
                    }

                    // Novo Som
                    currentAudio = new Audio(soundPath);
                    currentBtn = btn;
                    
                    // Atualiza Barra Flutuante
                    playerTitle.textContent = soundName;
                    floatingPlayer.classList.remove('hidden');

                    currentAudio.play().then(() => {
                        updateIcons('play');
                    }).catch(error => {
                        console.log("Erro ao tocar:", error);
                        // alert("Som não encontrado."); 
                    });

                    // Quando acaba
                    currentAudio.onended = () => {
                        updateIcons('reset');
                        floatingPlayer.classList.add('hidden');
                        currentAudio = null;
                        currentBtn = null;
                    };
                });
            });

            // Controlo da Barra Flutuante: Play/Pause Global
            playerControlBtn.addEventListener('click', () => {
                if (currentAudio) {
                    if (currentAudio.paused) {
                        currentAudio.play();
                        updateIcons('play');
                    } else {
                        currentAudio.pause();
                        updateIcons('pause');
                    }
                }
            });

            // Controlo da Barra Flutuante: Stop/Close Global
            playerCloseBtn.addEventListener('click', () => {
                if (currentAudio) {
                    currentAudio.pause();
                    currentAudio.currentTime = 0;
                    updateIcons('reset');
                    floatingPlayer.classList.add('hidden');
                    currentAudio = null;
                    currentBtn = null;
                }
            });
        });
    </script>

</body>
</html>