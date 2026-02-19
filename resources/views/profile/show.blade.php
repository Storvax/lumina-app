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

            /* Animações */
            .plant-grow { animation: grow 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); transform-origin: bottom center; }
            @keyframes grow { from { transform: scale(0); } to { transform: scale(1); } }
            
            .energy-bar { transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        </style>
    </x-slot>

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none bg-[#F8FAFC] dark:bg-slate-900 transition-colors duration-500">
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-indigo-200/30 dark:bg-indigo-900/20 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-lighten"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-teal-200/30 dark:bg-teal-900/20 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-lighten"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 pt-24 md:pt-32">

        <header class="relative bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] p-8 md:p-12 shadow-2xl mb-8 overflow-hidden text-white">
            <div class="absolute inset-0 opacity-20 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
            
            <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
                    <div class="relative group">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-24 h-24 md:w-32 md:h-32 rounded-3xl object-cover border-4 border-white/20 shadow-lg">
                        @else
                            <div class="w-24 h-24 md:w-32 md:h-32 rounded-3xl bg-white/10 border-4 border-white/20 flex items-center justify-center text-4xl font-bold backdrop-blur-sm">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="absolute -bottom-2 -right-2 bg-white text-indigo-600 p-2 rounded-xl shadow-lg hover:scale-110 transition-transform"><i class="ri-settings-3-fill"></i></a>
                    </div>
                    
                    <div>
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
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-4">
                            <div class="bg-white/10 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2 backdrop-blur-md">
                                <i class="ri-fire-fill text-orange-400"></i> {{ $stats['flames'] }} Chamas
                            </div>
                            <div class="bg-white/10 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2 backdrop-blur-md">
                                <i class="ri-calendar-check-fill text-teal-400"></i> {{ $stats['streak'] }} Dias Seguidos
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center items-center h-48 relative">
                    <div class="relative">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-orange-500/30 rounded-full blur-3xl animate-pulse"></div>

                        @if($stats['bonfire_level'] === 'spark')
                            <div class="flex flex-col items-center gap-2 animate-bounce">
                                <i class="ri-flashlight-fill text-6xl text-yellow-300 drop-shadow-[0_0_15px_rgba(253,224,71,0.8)]"></i>
                                <span class="text-xs font-bold text-yellow-200 uppercase tracking-widest bg-black/20 px-3 py-1 rounded-full">Faísca Inicial</span>
                            </div>
                        @elseif($stats['bonfire_level'] === 'flame')
                            <div class="flex flex-col items-center">
                                <i class="ri-fire-line text-8xl text-orange-400 animate-pulse drop-shadow-[0_0_20px_rgba(251,146,60,0.8)]"></i>
                                <span class="text-xs font-bold text-orange-200 uppercase tracking-widest bg-black/20 px-3 py-1 rounded-full mt-[-10px]">Chama Viva</span>
                            </div>
                        @elseif($stats['bonfire_level'] === 'bonfire')
                            <div class="flex flex-col items-center">
                                <div class="relative">
                                    <i class="ri-fire-fill text-9xl text-orange-600 absolute inset-0 animate-pulse opacity-80 blur-sm"></i>
                                    <i class="ri-fire-fill text-9xl text-yellow-500 relative z-10 drop-shadow-2xl"></i>
                                </div>
                                <span class="text-xs font-bold text-orange-100 uppercase tracking-widest bg-black/20 px-3 py-1 rounded-full mt-[-15px] relative z-20">Fogueira Acolhedora</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center">
                                <i class="ri-sun-fill text-9xl text-yellow-300 animate-[spin_12s_linear_infinite] drop-shadow-[0_0_30px_rgba(253,224,71,0.6)]"></i>
                                <span class="text-xs font-bold text-yellow-100 uppercase tracking-widest bg-black/20 px-3 py-1 rounded-full mt-4">Farol de Esperança</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-4 space-y-6">
                
                <div class="glass-card rounded-[2rem] p-6 relative overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-base"><i class="ri-flashlight-fill text-yellow-500"></i> Energia</h3>
                        <span class="text-xs font-bold text-slate-400">{{ $user->energy_level * 20 }}%</span>
                    </div>
                    
                    <div class="h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden mb-4 border border-slate-200 dark:border-slate-600 relative">
                        <div class="h-full bg-gradient-to-r from-yellow-400 to-orange-500 energy-bar" style="width: {{ $user->energy_level * 20 }}%"></div>
                    </div>

                    <div class="flex justify-between gap-1">
                        @for($i=1; $i<=5; $i++)
                            <button onclick="updateEnergy({{ $i }})" class="flex-1 h-8 rounded-lg border border-slate-100 dark:border-slate-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all text-xs font-bold text-slate-400 hover:text-indigo-600">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-6 bg-white dark:bg-slate-800">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-shield-heart-line text-rose-500"></i> Plano SOS
                        </h3>
                        <button onclick="document.getElementById('safety-modal').classList.remove('hidden')" class="text-xs font-bold text-indigo-500 hover:underline">Editar</button>
                    </div>
                    
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 h-40 overflow-y-auto custom-scrollbar">
                        @if($user->safety_plan)
                            <p class="text-sm text-slate-600 dark:text-slate-400 italic whitespace-pre-line">{{ is_array(json_decode($user->safety_plan)) ? 'Ver detalhes...' : $user->safety_plan }}</p>
                        @else
                            <p class="text-xs text-slate-400 text-center mt-8">O que fazer em caso de crise?<br>Clica em editar para definir.</p>
                        @endif
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-medal-line text-yellow-500"></i> Coleção de Conquistas
                        </h3>
                        <span class="text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 px-3 py-1 rounded-full">
                            {{ $stats['badges_count'] }}
                        </span>
                    </div>

                    <div class="glass-card rounded-[2rem] p-6 md:p-8">
                        @foreach($achievements as $badge)
                            @php $isUnlocked = in_array($badge->id, $unlockedIds); @endphp
                            
                            <div class="relative group">
                                <div class="aspect-square rounded-2xl flex flex-col items-center justify-center p-3 border transition-all duration-300
                                    {{ $isUnlocked 
                                        ? 'bg-'.$badge->color.'-50 dark:bg-'.$badge->color.'-900/20 border-'.$badge->color.'-100 dark:border-'.$badge->color.'-800' 
                                        : 'bg-slate-50 dark:bg-slate-800/50 border-slate-100 dark:border-slate-700 grayscale opacity-50' 
                                    }}">
                                    <i class="{{ $badge->icon }} text-3xl mb-2 {{ $isUnlocked ? 'text-'.$badge->color.'-500' : 'text-slate-400' }}"></i>
                                    <p class="text-[10px] font-bold text-center leading-tight {{ $isUnlocked ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400' }}">
                                        {{ $badge->name }}
                                    </p>
                                </div>
                                
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-40 bg-slate-900 text-white text-xs p-3 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-20 shadow-xl">
                                    <p class="font-bold mb-1">{{ $badge->name }}</p>
                                    <p class="opacity-80 text-[10px]">{{ $badge->description }}</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 text-[10px] font-bold {{ $isUnlocked ? 'text-green-400' : 'text-yellow-400' }}">
                                        {{ $isUnlocked ? 'Conquistado!' : 'Bloqueado' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-6">
                
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

                    <div class="glass-card rounded-[2rem] p-6 md:p-8 mt-6 mb-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                    <i class="ri-line-chart-line text-indigo-500"></i> Ondas de Humor
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Os teus últimos 30 dias.</p>
                            </div>
                        </div>
                        
                        <div class="h-64 w-full relative">
                            @if(isset($chartData) && count($chartData) > 2)
                                <canvas id="moodChart"></canvas>
                            @else
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                                    <i class="ri-bar-chart-2-line text-4xl mb-2 opacity-50"></i>
                                    <p class="text-sm">Precisamos de pelo menos 3 registos para gerar o gráfico.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-2 md:gap-3">
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
                    </div>
                </div>

            </div>

            <div class="col-span-1 lg:col-span-12 mt-4">
                <div class="glass-card rounded-[2rem] p-6 md:p-8 relative">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-lg md:text-xl flex items-center gap-2">
                                <i class="ri-map-pin-line text-indigo-500"></i> A Minha Jornada
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Marcos importantes do teu percurso. (Privado por predefinição)</p>
                        </div>
                        <button onclick="document.getElementById('milestone-modal').classList.remove('hidden')" class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 font-bold px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2 border border-indigo-100 dark:border-indigo-800">
                            <i class="ri-add-line"></i> Novo Marco
                        </button>
                    </div>

                    @if(isset($milestones) && $milestones->isEmpty())
                        <div class="text-center py-8 bg-slate-50/50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <p class="text-slate-400 text-sm">Ainda não registaste nenhum marco.<br>Celebra as pequenas vitórias: "Primeiro dia de terapia", "Consegui sair de casa".</p>
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
                                            <div class="mt-2 flex items-center gap-2">
                                                @if($milestone->is_public)
                                                    <span class="text-[10px] bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 px-2 py-0.5 rounded flex items-center gap-1 font-bold"><i class="ri-global-line"></i> Público</span>
                                                @else
                                                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 px-2 py-0.5 rounded flex items-center gap-1 font-bold"><i class="ri-lock-line"></i> Privado</span>
                                                @endif
                                            </div>
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

    <div id="safety-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-4 dark:text-white flex items-center gap-2"><i class="ri-shield-heart-line text-rose-500"></i> Plano de Segurança</h3>
            <p class="text-sm text-slate-500 mb-4">Escreve o que te ajuda em momentos de crise. (Ex: "Ligar à mãe", "Ouvir a playlist calma", "Respirar 4-7-8")</p>
            
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

    <div id="tags-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-2 dark:text-white">A tua identidade</h3>
            <p class="text-sm text-slate-500 mb-6">Escolhe até 3 áreas que definem a tua jornada atual. Isto ajuda a conectar-te com pessoas que passam pelo mesmo.</p>
            
            <form action="{{ route('profile.tags') }}" method="POST">
                @csrf
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($tagsList as $tagOption)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tagOption }}" class="peer sr-only" 
                                {{ in_array($tagOption, $user->emotional_tags ?? []) ? 'checked' : '' }}
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

    <div id="milestone-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl animate-fade-up">
            <h3 class="text-xl font-bold mb-6 dark:text-white">Novo Marco</h3>
            
            <form action="{{ route('profile.milestones.store') }}" method="POST">
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
                        <p class="text-xs text-slate-500 ml-6 mt-1">Se não marcares, só tu podes ver este marco.</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('milestone-modal').classList.add('hidden')" class="px-4 py-2 text-slate-500 font-bold hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all">Adicionar à Jornada</button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($chartData) && count($chartData) > 2)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('moodChart').getContext('2d');
            
            const rawData = @json($chartData);
            const labels = rawData.map(item => item.date);
            const dataPoints = rawData.map(item => item.mood);
    
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');
    
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Humor',
                        data: dataPoints,
                        borderColor: '#4f46e5',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold' },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    const moods = ['', '1 - Muito Difícil', '2 - Difícil', '3 - Neutro', '4 - Bom', '5 - Excelente'];
                                    return moods[context.raw];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 1,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    const emojis = ['', '🥀', '🌱', '🌿', '🌷', '🌻'];
                                    return emojis[value];
                                }
                            },
                            grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#64748b', font: { size: 10 } }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        });
    </script>
    @endif

    <script>
        async function updateEnergy(level) {
            const bar = document.querySelector('.energy-bar');
            if(bar) bar.style.width = (level * 20) + '%';
            try { await axios.post('{{ route("profile.energy") }}', { level: level }); } catch(e) { console.error(e); }
        }

        // Limita checkboxes a 3 no modal de tags
        function checkMaxTags(checkbox) {
            const checked = document.querySelectorAll('input[name="tags[]"]:checked');
            if (checked.length > 3) {
                checkbox.checked = false;
                alert('Podes escolher no máximo 3 tags para manter o foco da tua jornada.');
            }
        }
    </script>

</x-lumina-layout>