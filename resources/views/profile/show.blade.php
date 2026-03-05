<x-lumina-layout title="O Meu Refúgio | Lumina">

    <x-slot name="css">
        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            
            /* Vidro Otimizado */
            .glass-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.9);
                box-shadow: 0 4px 20px -2px rgba(200, 210, 255, 0.3);
            }
            .dark .glass-card {
                background: rgba(30, 41, 59, 0.7);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: none;
            }

            /* Animações Originais */
            .plant-grow { animation: grow 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); transform-origin: bottom center; }
            @keyframes grow { from { transform: scale(0); } to { transform: scale(1); } }
            
            .energy-bar { transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
            @keyframes shimmer { 100% { transform: translateX(100%); } }

            /* Clima da Alma (Chuva no Guardião) */
            .rain-container { 
                position: absolute; 
                inset: 0; 
                overflow: hidden; 
                pointer-events: none; 
                border-radius: inherit; 
            }
            .drop { 
                position: absolute; 
                width: 2px; 
                height: 15px; 
                background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0.6)); 
                animation: fall linear infinite; 
            }
            @keyframes fall {
                0% { transform: translateY(-20px); opacity: 0; }
                10% { opacity: 1; }
                80% { opacity: 1; }
                100% { transform: translateY(180px); opacity: 0; }
            }
            @keyframes bounceSlight {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-4px); }
            }
        </style>
    </x-slot>

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none bg-[#F8FAFC] dark:bg-slate-900 transition-colors duration-500">
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-indigo-200/30 dark:bg-indigo-900/20 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-lighten"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-teal-200/30 dark:bg-teal-900/20 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-lighten"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 pt-24 md:pt-32">

        {{-- CABEÇALHO --}}
        <header class="relative bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] p-8 md:p-12 shadow-2xl mb-8 overflow-hidden text-white">
            <div class="absolute inset-0 opacity-20 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
            
            <div class="relative z-10 grid md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-7 lg:col-span-8 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
                    <div class="relative group shrink-0">
                        <div class="w-24 h-24 md:w-32 md:h-32 rounded-3xl bg-white/10 border-4 border-white/20 flex items-center justify-center text-4xl font-bold backdrop-blur-sm">
                            <img src="https://api.dicebear.com/7.x/notionists/svg?seed={{ $user->name }}" class="w-full h-full rounded-full object-cover" loading="lazy" alt="Avatar de {{ $user->name }}">
                        </div>
                        <a href="{{ route('profile.edit') }}" class="absolute -bottom-2 -right-2 bg-white text-indigo-600 p-2 rounded-xl shadow-lg hover:scale-110 transition-transform"><i class="ri-settings-3-fill"></i></a>
                    </div>
                    
                    <div class="w-full">
                        <h1 class="text-3xl md:text-5xl font-black tracking-tight mb-2">{{ $user->name }}</h1>
                        <p class="text-indigo-100 text-sm md:text-base font-medium max-w-md mx-auto md:mx-0 opacity-90">
                            {{ $user->bio ?? '"A cuidar de mim, um dia de cada vez."' }}
                        </p>

                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-3 items-center">
                            @if(is_array($user->emotional_tags) && count($user->emotional_tags) > 0)
                                @foreach($user->emotional_tags as $tag)
                                    <span class="px-3 py-1 rounded-full bg-white/10 text-indigo-100 text-[10px] uppercase tracking-wider font-bold border border-white/20 backdrop-blur-md shadow-sm">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-xs text-indigo-300 italic opacity-70">Nenhuma tag definida...</span>
                            @endif
                            
                            <button onclick="document.getElementById('tags-modal').classList.remove('hidden')" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors border border-white/20 ml-1" title="Editar Tags">
                                <i class="ri-edit-line text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- WIDGET: O GUARDIÃO DA CALMA (TERRÁRIO + CLIMA DA ALMA) --}}
                @php
                    // Variáveis Mock para o Guardião (O Claude ligará à BD)
                    $guardian = $guardianStatus ?? [
                        'stage_name' => 'Broto de Coragem',
                        'emoji' => '🌿',
                        'color' => 'emerald',
                        'current_flames' => $stats['flames'] ?? 85,
                        'next_stage_flames' => 150,
                        'progress_percent' => 56,
                        'weather' => 'rainy', // 'sunny' ou 'rainy' (muda aqui para testar)
                    ];
                    $weather = $guardian['weather'];
                @endphp
                <div class="md:col-span-5 lg:col-span-4 flex flex-col items-center justify-center bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20 relative overflow-hidden group">
                    
                    {{-- Efeito de Fundo do Clima --}}
                    <div class="absolute inset-0 {{ $weather === 'rainy' ? 'bg-slate-900/30' : 'bg-gradient-to-tr from-emerald-500/10 to-teal-500/10' }} transition-colors duration-1000 pointer-events-none"></div>

                    {{-- Animação de Chuva --}}
                    @if($weather === 'rainy')
                        <div class="rain-container z-0 opacity-70">
                            @for($i = 0; $i < 20; $i++)
                                <div class="drop" style="left: {{ rand(2, 98) }}%; animation-duration: {{ rand(8, 14)/10 }}s; animation-delay: -{{ rand(0, 20)/10 }}s;"></div>
                            @endfor
                        </div>
                        <div class="absolute top-0 w-full h-20 bg-slate-900/50 blur-xl z-0 pointer-events-none"></div>
                    @endif

                    <div class="relative h-28 w-full flex items-center justify-center mb-3 z-10">
                        {{-- Brilho de fundo suave --}}
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 {{ $weather === 'rainy' ? 'bg-blue-500/20' : 'bg-emerald-500/30' }} rounded-full blur-2xl transition-colors duration-1000"></div>

                        {{-- Redoma de Vidro --}}
                        <div class="relative w-24 h-32 rounded-t-full rounded-b-2xl {{ $weather === 'rainy' ? 'bg-slate-800/40 border-slate-500/30' : 'bg-white/20 border-white/40' }} backdrop-blur-sm border-2 shadow-[inset_0_-10px_20px_rgba(0,0,0,0.1)] flex items-center justify-center overflow-hidden transition-colors duration-1000">
                            
                            {{-- Reflexo do Vidro --}}
                            <div class="absolute top-1 left-2 w-4 h-14 bg-white/30 rounded-full blur-[1px] transform -rotate-12 pointer-events-none z-30"></div>
                            
                            {{-- O Avatar / Emoji --}}
                            <div class="text-5xl filter drop-shadow-lg transform transition-transform duration-700 group-hover:scale-110 animate-[bounceSlight_4s_ease-in-out_infinite] z-10 {{ $weather === 'rainy' ? 'opacity-90 grayscale-[30%]' : '' }}">
                                {{ $guardian['emoji'] }}
                            </div>
                            
                            {{-- Base de Terra/Água --}}
                            <div class="absolute bottom-0 w-full h-6 z-10 {{ $weather === 'rainy' ? 'bg-gradient-to-t from-blue-900/70 to-blue-900/10' : 'bg-gradient-to-t from-emerald-900/60 to-transparent' }} transition-colors duration-1000"></div>
                        </div>
                    </div>

                    <div class="text-center w-full z-10">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest mb-2 {{ $weather === 'rainy' ? 'bg-blue-900/60 text-blue-200 border border-blue-500/30' : 'bg-emerald-500/30 text-emerald-100 border border-emerald-400/30' }} transition-colors duration-1000">
                            <i class="{{ $weather === 'rainy' ? 'ri-rainy-fill' : 'ri-sun-fill' }}"></i> 
                            {{ $weather === 'rainy' ? 'Tempo de Recolhimento' : 'Guardião da Calma' }}
                        </div>
                        
                        <h2 class="text-lg font-black text-white mb-1 leading-tight">
                            {{ $guardian['stage_name'] }}
                        </h2>
                        
                        <p class="text-[10px] text-indigo-100/80 mb-4 leading-tight max-w-[220px] mx-auto min-h-[28px]">
                            @if($weather === 'rainy')
                                A chuva lá fora reflete o teu dia. <br>Está tudo bem em não estar bem.
                            @else
                                O teu esforço diário está a dar frutos. Continua a regar a tua mente.
                            @endif
                        </p>

                        <div class="w-full">
                            <div class="flex justify-between text-[10px] font-bold text-indigo-100 mb-1.5 px-1">
                                <span class="flex items-center gap-1">
                                    <i class="ri-fire-fill {{ $weather === 'rainy' ? 'text-blue-300' : 'text-orange-400' }}"></i> {{ $guardian['current_flames'] }} totais
                                </span>
                                <span class="opacity-80">Evolui: {{ $guardian['next_stage_flames'] }}</span>
                            </div>
                            <div class="h-2 w-full bg-black/30 rounded-full overflow-hidden p-[1px] border border-white/5">
                                <div class="h-full bg-gradient-to-r {{ $weather === 'rainy' ? 'from-blue-400 to-indigo-400' : 'from-emerald-400 to-teal-400' }} rounded-full relative transition-all duration-1500" style="width: {{ $guardian['progress_percent'] }}%">
                                    <div class="absolute inset-0 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.4),transparent)] animate-[shimmer_2s_infinite] -translate-x-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-4 space-y-6">
                
                {{-- WIDGET DE ENERGIA --}}
                <div class="glass-card rounded-[2rem] p-6 relative overflow-hidden" x-data="{ 
                    energy: {{ $user->energy_level ?? 3 }},
                    async saveEnergy(level) {
                        this.energy = level;
                        try { await axios.post('{{ route("profile.energy") }}', { level: level }); } catch(e) { console.error(e); }
                    }
                }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-base"><i class="ri-flashlight-fill text-yellow-500"></i> Energia</h3>
                        <span class="text-xs font-bold text-slate-400" x-text="(energy * 20) + '%'">{{ $user->energy_level * 20 }}%</span>
                    </div>
                    
                    <div class="h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden mb-4 border border-slate-200 dark:border-slate-600 relative">
                        <div class="h-full bg-gradient-to-r from-yellow-400 to-orange-500 energy-bar" :style="'width: ' + (energy * 20) + '%'"></div>
                    </div>

                    <div class="flex justify-between gap-1">
                        @for($i=1; $i<=5; $i++)
                            <button @click="saveEnergy({{ $i }})" class="flex-1 h-8 rounded-lg border transition-all text-xs font-bold"
                                :class="energy == {{ $i }} ? 'bg-orange-100 border-orange-200 text-orange-600 dark:bg-orange-900/30 dark:border-orange-800' : 'border-slate-100 dark:border-slate-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-slate-400 hover:text-indigo-600'">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>

                {{-- WIDGET PLANO SOS --}}
                <div class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-800">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-shield-heart-line text-rose-500"></i> Plano SOS
                        </h3>
                        <button onclick="document.getElementById('safety-modal').classList.remove('hidden')" class="text-xs font-bold text-indigo-500 hover:underline">Editar</button>
                    </div>
                    
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 h-40 overflow-y-auto custom-scrollbar">
                        @if($user->safety_plan)
                            <p class="text-sm text-slate-600 dark:text-slate-400 italic whitespace-pre-line">{{ is_array(json_decode($user->safety_plan)) ? 'Ver detalhes no modal de edição...' : $user->safety_plan }}</p>
                        @else
                            <p class="text-xs text-slate-400 text-center mt-8">O que fazer em caso de crise?<br>Clica em editar para definir.</p>
                        @endif
                    </div>
                </div>

                {{-- WIDGET CONQUISTAS --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-medal-line text-yellow-500"></i> Conquistas
                        </h3>
                        <span class="text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 px-3 py-1 rounded-full">
                            {{ $stats['badges_count'] ?? 0 }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        @foreach($allAchievements as $badge)
                            @php 
                                $isUnlocked = isset($unlockedIds) && in_array($badge->id, $unlockedIds); 
                                $color = $badge->color ?? 'orange';
                                $icon = $badge->icon ?? 'ri-medal-line';
                                $name = $badge->name ?? 'Conquista';
                                $description = $badge->description ?? '';
                            @endphp
                            
                            <div class="relative group">
                                <div class="aspect-square rounded-2xl flex flex-col items-center justify-center p-2 border transition-all duration-300 cursor-default
                                    {{ $isUnlocked 
                                        ? 'bg-'.$color.'-50 dark:bg-'.$color.'-900/20 border-'.$color.'-100 dark:border-'.$color.'-800' 
                                        : 'bg-slate-50 dark:bg-slate-800/50 border-slate-100 dark:border-slate-700 grayscale opacity-40 hover:opacity-70' 
                                    }}">
                                    <i class="{{ $icon }} text-2xl md:text-3xl mb-1 {{ $isUnlocked ? 'text-'.$color.'-500 drop-shadow-sm' : 'text-slate-400' }}"></i>
                                    <p class="text-[9px] font-bold text-center leading-tight {{ $isUnlocked ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400' }}">
                                        {{ $name }}
                                    </p>
                                </div>
                                
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-40 bg-slate-900 text-white text-xs p-3 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-20 shadow-xl">
                                    <p class="font-bold mb-1">{{ $name }}</p>
                                    <p class="opacity-80 text-[10px]">{{ $description }}</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 text-[10px] font-bold {{ $isUnlocked ? 'text-green-400' : 'text-slate-400' }}">
                                        @if($isUnlocked) <i class="ri-check-line"></i> Conquistado! @else <i class="ri-lock-line"></i> Bloqueado @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-6">
                
                {{-- O JARDIM / ESPIRAL DE HUMOR --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-8 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                                <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">O Teu Jardim</span>
                                <i class="ri-plant-line text-emerald-500"></i>
                            </h2>
                            <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">Regista o teu humor para cultivar flores.</p>
                        </div>
                        <a href="{{ route('diary.index') }}" class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg flex items-center gap-2 hover:scale-105 transition-transform">
                            <i class="ri-add-line"></i> Regar Hoje
                        </a>
                    </div>

                    <div class="glass-card rounded-[2rem] p-6 md:p-8 mt-6 mb-6 border border-slate-100 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-lg">
                                    <i class="ri-donut-chart-line text-indigo-500"></i> Espiral de Humor
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cada dia é um ponto novo. Vê como a tua perspetiva evolui.</p>
                            </div>
                        </div>
                        
                        <div class="w-full relative flex justify-center py-4">
                            @if(isset($spiralData) && count($spiralData) > 0)
                                <x-mood-spiral :data="$spiralData" />
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-400 py-12">
                                    <i class="ri-leaf-line text-5xl mb-3 opacity-30"></i>
                                    <p class="text-sm">Regista as tuas emoções no diário para desenhar a tua espiral.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-2 md:gap-3">
                        @if(isset($garden) && is_array($garden) && count($garden) > 0)
                            @foreach($garden as $plot)
                                @if($plot['type'] === 'plant')
                                    <div class="aspect-square bg-emerald-50/50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800 flex flex-col items-center justify-center relative group transition-all hover:bg-emerald-100 dark:hover:bg-emerald-900/40" title="Humor: {{ $plot['mood'] }}/5">
                                        <div class="text-2xl md:text-3xl plant-grow drop-shadow-sm">{{ $plot['icon'] }}</div>
                                        <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $plot['date'] }}</span>
                                    </div>
                                @else
                                    <div class="aspect-square bg-slate-50 dark:bg-slate-800/50 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center opacity-60">
                                        <span class="text-[9px] font-bold text-slate-400">{{ $plot['date'] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-span-7 flex justify-center py-4 text-slate-400 text-sm">O teu jardim ainda não tem registos recentes.</div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- SECÇÃO LARGURA TOTAL: IMPACTO REAL DA COMUNIDADE --}}
            @php
                // Mock de Variáveis de Impacto
                $globalImpact = $globalImpact ?? [
                    'current_flames' => 8450,
                    'target_flames' => 10000,
                    'percentage' => 84,
                    'user_contribution' => $stats['flames'] ?? 0,
                    'goal_name' => 'Plantar 1 Árvore em Portugal',
                    'org_name' => 'Associação Florestal'
                ];
            @endphp
            <div class="col-span-1 lg:col-span-12 mt-2">
                <div class="relative bg-gradient-to-br from-teal-600 to-emerald-800 rounded-[2.5rem] p-8 md:p-10 text-white shadow-xl overflow-hidden group">
                    <div class="absolute right-0 top-0 w-1/2 h-full opacity-10 pointer-events-none">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="absolute w-[400px] h-[400px] -right-20 -top-20 animate-[spin_60s_linear_infinite]">
                            <path fill="#ffffff" d="M44.7,-76.4C58.8,-69.2,71.8,-59.1,79.6,-45.8C87.4,-32.6,90,-16.3,89.1,-0.5C88.1,15.3,83.6,30.6,75.2,43.6C66.8,56.6,54.5,67.3,40.7,75.1C26.9,82.9,11.5,87.8,-3.8,89.5C-19.1,91.2,-38.2,89.7,-52.6,81.5C-67,73.3,-76.8,58.4,-83.4,42.5C-90,26.6,-93.4,9.7,-91.3,-6.4C-89.2,-22.5,-81.6,-37.8,-70.7,-49.6C-59.8,-61.4,-45.6,-69.7,-31.6,-76.6C-17.6,-83.5,-3.8,-89,10.6,-86.3C25,-83.6,44.7,-76.4,44.7,-76.4Z" transform="translate(100 100)" />
                        </svg>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                        <div class="flex-1 text-center md:text-left">
                            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-4">
                                <i class="ri-earth-fill"></i> O Nosso Impacto Real
                            </div>
                            <h3 class="text-3xl md:text-4xl font-black mb-3">As tuas chamas geram vida.</h3>
                            <p class="text-emerald-50 text-sm md:text-base leading-relaxed max-w-xl mx-auto md:mx-0">
                                Quando a comunidade Lumina atingir a meta, doaremos o equivalente para <strong>{{ $globalImpact['goal_name'] }}</strong> via {{ $globalImpact['org_name'] }}.
                            </p>
                            
                            <div class="mt-6 inline-flex items-center gap-3 bg-black/20 rounded-2xl p-3 border border-white/10">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-2xl text-orange-400">
                                    <i class="ri-fire-fill"></i>
                                </div>
                                <div class="text-left pr-4">
                                    <p class="text-[10px] text-emerald-200 uppercase tracking-widest font-bold">A Tua Contribuição</p>
                                    <p class="font-black text-lg">{{ $globalImpact['user_contribution'] }} <span class="text-sm font-medium opacity-80">chamas</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-5/12 bg-white/10 backdrop-blur-lg border border-white/20 p-6 rounded-3xl text-center">
                            <i class="ri-tree-fill text-6xl text-emerald-300 mb-2 drop-shadow-lg inline-block transform transition-transform group-hover:scale-110 duration-500"></i>
                            <div class="flex justify-between text-xs font-bold text-emerald-100 mb-2 mt-4">
                                <span>{{ number_format($globalImpact['current_flames']) }} alcançadas</span>
                                <span>Alvo: {{ number_format($globalImpact['target_flames']) }}</span>
                            </div>
                            <div class="h-4 bg-black/30 rounded-full overflow-hidden p-[2px]">
                                <div class="h-full bg-gradient-to-r from-emerald-300 to-yellow-300 rounded-full relative transition-all duration-1500" style="width: {{ $globalImpact['percentage'] }}%">
                                    <div class="absolute inset-0 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.4),transparent)] animate-[shimmer_2s_infinite] -translate-x-full"></div>
                                </div>
                            </div>
                            <p class="text-[10px] text-emerald-200 mt-3 font-medium uppercase tracking-widest">Juntos somos uma floresta</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECÇÃO DE MARCOS DA JORNADA --}}
            <div class="col-span-1 lg:col-span-12 mt-4">
                <div class="glass-card rounded-[2rem] p-6 md:p-8 relative">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-lg md:text-xl flex items-center gap-2">
                                <i class="ri-map-pin-line text-indigo-500"></i> A Minha Jornada
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Marcos importantes do teu percurso.</p>
                        </div>
                        <button onclick="document.getElementById('milestone-modal').classList.remove('hidden')" class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 font-bold px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2 border border-indigo-100 dark:border-indigo-800">
                            <i class="ri-add-line"></i> Novo Marco
                        </button>
                    </div>

                    @if(isset($milestones) && $milestones->isEmpty())
                        <div class="text-center py-8 bg-slate-50/50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <p class="text-slate-400 text-sm">Ainda não registaste nenhum marco.<br>Celebra as pequenas vitórias!</p>
                        </div>
                    @elseif(isset($milestones))
                        <div class="relative border-l-2 border-indigo-100 dark:border-indigo-900/50 ml-3 md:ml-4 space-y-6">
                            @foreach($milestones as $milestone)
                                <div class="relative pl-6 md:pl-8 group">
                                    <div class="absolute w-4 h-4 rounded-full bg-indigo-500 border-4 border-white dark:border-slate-800 -left-[9px] top-1 shadow-sm"></div>
                                    
                                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-5 border border-slate-100 dark:border-slate-700 shadow-sm transition-all group-hover:shadow-md flex justify-between items-start">
                                        <div>
                                            <span class="text-xs font-bold text-indigo-500 mb-1 block">{{ $milestone->date->format('d M, Y') }}</span>
                                            <h4 class="text-slate-800 dark:text-white font-bold">{{ $milestone->title }}</h4>
                                        </div>
                                        
                                        <form action="{{ route('profile.milestones.destroy', $milestone) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition-colors p-1" title="Remover marco" onclick="return confirm('Apagar este marco?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- MODAIS --}}
    <div id="safety-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-4 dark:text-white flex items-center gap-2"><i class="ri-shield-heart-line text-rose-500"></i> Plano de Segurança</h3>
            <p class="text-sm text-slate-500 mb-4">Escreve o que te ajuda em momentos de crise.</p>
            <form action="{{ route('profile.safety') }}" method="POST">
                @csrf
                <textarea name="safety_plan" rows="6" class="w-full rounded-2xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white mb-6 p-4 focus:ring-indigo-500" placeholder="Escreve aqui...">{{ is_string($user->safety_plan) ? $user->safety_plan : '' }}</textarea>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('safety-modal').classList.add('hidden')" class="px-5 py-2.5 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all">Guardar Plano</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tags-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-2 dark:text-white">A tua identidade</h3>
            <p class="text-sm text-slate-500 mb-6">Escolhe até 3 áreas que definem a tua jornada atual.</p>
            <form action="{{ route('profile.tags') }}" method="POST">
                @csrf
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($tagsList as $tagOption)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tagOption }}" class="peer sr-only" 
                                {{ is_array($user->emotional_tags) && in_array($tagOption, $user->emotional_tags) ? 'checked' : '' }}
                                onclick="checkMaxTags(this)">
                            <span class="px-4 py-2 rounded-full border border-slate-200 dark:border-slate-600 text-sm font-medium text-slate-600 dark:text-slate-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition-all select-none block">
                                {{ $tagOption }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('tags-modal').classList.add('hidden')" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="milestone-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-6 dark:text-white">Novo Marco</h3>
            <form action="{{ route('profile.milestones.store') ?? '#' }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">O que conquistaste?</label>
                        <input type="text" name="title" required placeholder="Ex: Primeiro dia de terapia" class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Quando aconteceu?</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-indigo-500">
                    </div>
                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Tornar público no meu perfil</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('milestone-modal').classList.add('hidden')" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all">Adicionar à Jornada</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function checkMaxTags(checkbox) {
            const checked = document.querySelectorAll('input[name="tags[]"]:checked');
            if (checked.length > 3) {
                checkbox.checked = false;
                alert('Podes escolher no máximo 3 tags para manter o foco da tua jornada.');
            }
        }
    </script>
</x-lumina-layout>