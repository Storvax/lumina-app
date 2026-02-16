<x-lumina-layout title="O Meu Refúgio | Lumina">

    <x-slot name="css">
        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            
            /* Vidro Otimizado */
            .glass-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.8);
                box-shadow: 0 4px 20px -2px rgba(200, 210, 255, 0.3);
            }

            /* Animação Planta */
            .plant-grow { animation: grow 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); transform-origin: bottom center; }
            @keyframes grow { from { transform: scale(0); } to { transform: scale(1); } }
            
            .energy-bar { transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        </style>
    </x-slot>

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none bg-[#F8FAFC]">
        <div class="absolute top-0 right-0 w-[300px] md:w-[800px] h-[300px] md:h-[800px] bg-indigo-200/20 rounded-full blur-[80px] md:blur-[120px] mix-blend-multiply"></div>
        <div class="absolute bottom-0 left-0 w-[250px] md:w-[600px] h-[250px] md:h-[600px] bg-teal-200/20 rounded-full blur-[80px] md:blur-[120px] mix-blend-multiply"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 pt-24 md:pt-32">

        <header class="relative bg-white rounded-[2rem] p-6 md:p-8 shadow-xl shadow-slate-200/50 mb-8 overflow-hidden border border-white">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#4f46e5_1px,transparent_1px)] [background-size:16px_16px]"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row gap-6 md:gap-8 items-center md:items-end">
                <div class="relative group shrink-0">
                    <div class="w-24 h-24 md:w-32 md:h-32 rounded-[1.5rem] md:rounded-[2rem] overflow-hidden border-4 border-white shadow-2xl bg-indigo-50">
                        <img src="https://api.dicebear.com/7.x/notionists/svg?seed={{ $user->name }}&backgroundColor=c7d2fe" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute -bottom-2 -right-2 md:-bottom-3 md:-right-3 bg-slate-900 text-white text-[10px] md:text-xs font-bold px-2 py-0.5 md:px-3 md:py-1 rounded-full border-4 border-white shadow-lg">
                        Lvl {{ $stats['level'] }}
                    </div>
                </div>
                
                <div class="flex-1 text-center md:text-left w-full">
                    <h1 class="text-2xl md:text-4xl font-black text-slate-800 tracking-tight mb-2">{{ $user->name }}</h1>
                    
                    <div class="bg-slate-50/80 backdrop-blur rounded-xl p-3 inline-block border border-slate-100 max-w-xl mx-auto md:mx-0">
                        <p class="text-slate-600 text-xs md:text-sm italic flex items-center justify-center md:justify-start gap-2">
                            {{ $user->bio ?? '"Ainda a escrever a minha história..."' }}
                            <a href="{{ route('profile.edit') }}" class="text-indigo-500 hover:text-indigo-700"><i class="ri-pencil-line"></i></a>
                        </p>
                    </div>

                    <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-4">
                        <span class="px-2 py-1 md:px-3 md:py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[10px] md:text-xs font-bold border border-indigo-100">🧠 Criativo</span>
                        <span class="px-2 py-1 md:px-3 md:py-1 rounded-lg bg-teal-50 text-teal-700 text-[10px] md:text-xs font-bold border border-teal-100">🛡️ Guardião</span>
                    </div>
                </div>

                <div class="flex gap-2 w-full md:w-auto justify-center md:justify-end">
                    <a href="{{ route('profile.edit') }}" class="flex-1 md:flex-none py-2 md:py-0 w-auto md:w-12 h-10 md:h-12 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-200 transition-all flex items-center justify-center text-sm font-bold gap-2">
                        <i class="ri-settings-line text-lg"></i> <span class="md:hidden">Editar</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-4 space-y-6">
                
                <div class="glass-card rounded-[2rem] p-6 relative overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base md:text-lg"><i class="ri-flashlight-fill text-yellow-500"></i> Energia</h3>
                        <span class="text-xs font-bold text-slate-400">{{ $user->energy_level * 20 }}%</span>
                    </div>
                    
                    <div class="h-3 md:h-4 bg-slate-100 rounded-full overflow-hidden mb-4 border border-slate-200 relative">
                        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
                        <div class="h-full bg-gradient-to-r from-yellow-400 to-orange-500 energy-bar relative" style="width: {{ $user->energy_level * 20 }}%"></div>
                    </div>

                    <div class="flex justify-between gap-1">
                        @for($i=1; $i<=5; $i++)
                            <button onclick="updateEnergy({{ $i }})" class="flex-1 h-8 md:h-10 rounded-lg border border-slate-100 hover:border-indigo-300 hover:bg-indigo-50 transition-all text-xs font-bold text-slate-400 hover:text-indigo-600">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-6 bg-gradient-to-br from-indigo-600 to-violet-700 text-white text-center relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                            <i class="ri-mail-star-line text-xl"></i>
                        </div>
                        <h3 class="font-bold text-base md:text-lg">Para Dias Maus</h3>
                        <p class="text-indigo-100 text-xs mb-4 px-2">Uma mensagem do teu "eu" do passado.</p>
                        <button class="bg-white text-indigo-700 font-bold text-xs py-2.5 px-6 rounded-xl shadow-lg w-full">
                            Ler / Escrever
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="glass-card rounded-[2rem] p-6 md:p-8 h-full relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h2 class="text-xl md:text-2xl font-black text-slate-800 flex items-center gap-2">
                                <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">O Teu Jardim</span>
                                <i class="ri-plant-line text-emerald-500"></i>
                            </h2>
                            <p class="text-slate-500 text-xs md:text-sm mt-1">Cultiva a tua mente.</p>
                        </div>
                        <a href="{{ route('diary.index') }}" class="w-full sm:w-auto bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg flex items-center justify-center gap-2">
                            <i class="ri-add-line"></i> Regar Hoje
                        </a>
                    </div>

                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 md:gap-4">
                        @foreach($garden as $plot)
                            @if($plot['type'] === 'plant')
                                <div class="aspect-square bg-emerald-50/50 rounded-xl border border-emerald-100 flex flex-col items-center justify-center relative group" title="{{ $plot['mood'] }}/5">
                                    <div class="text-2xl md:text-4xl plant-grow">{{ $plot['icon'] }}</div>
                                    <span class="text-[8px] md:text-[10px] font-bold text-emerald-700 mt-1">{{ $plot['date'] }}</span>
                                </div>
                            @else
                                <div class="aspect-square bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center opacity-60">
                                    <i class="ri-seedling-line text-slate-300 text-lg md:text-xl"></i>
                                    <span class="text-[8px] md:text-[10px] font-bold text-slate-400 mt-1">{{ $plot['date'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-6 pt-4 border-t border-slate-100">
                        <div class="text-center">
                            <span class="block text-lg md:text-2xl font-black text-slate-800">{{ $stats['streak'] }}</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">Dias</span>
                        </div>
                        <div class="text-center border-l border-slate-100">
                            <span class="block text-lg md:text-2xl font-black text-slate-800">{{ $stats['total_logs'] }}</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">Flores</span>
                        </div>
                        <div class="text-center border-l border-slate-100">
                            <span class="block text-lg md:text-2xl font-black text-slate-800">{{ $stats['level'] }}</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">Nível</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-12 mt-2">
                <div class="glass-card rounded-[2rem] p-6 md:p-8 border border-slate-200/60">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-slate-800 text-base md:text-lg flex items-center gap-2">
                            <i class="ri-archive-line text-rose-500"></i> LifeBox
                        </h3>
                        <button class="text-[10px] font-bold bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg">Gerir</button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                        <div class="group relative aspect-video rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 p-3 text-white flex flex-col justify-end overflow-hidden cursor-pointer">
                            <i class="ri-spotify-fill text-3xl absolute top-2 right-2 opacity-30"></i>
                            <span class="font-bold text-xs">Playlist</span>
                        </div>
                        <div class="aspect-video rounded-xl bg-amber-50 border border-amber-100 p-3 flex items-center justify-center text-center">
                            <p class="font-serif italic text-amber-800 text-xs">"Isto também passa."</p>
                        </div>
                        <button class="aspect-video rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 gap-1 hover:bg-slate-50">
                            <i class="ri-add-circle-line text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        async function updateEnergy(level) {
            const bar = document.querySelector('.energy-bar');
            bar.style.width = (level * 20) + '%';
            try { await axios.post('{{ route("profile.energy") }}', { level: level }); } catch(e) { console.error(e); }
        }
    </script>

</x-lumina-layout>