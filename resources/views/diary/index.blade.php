<x-lumina-layout title="O Meu Diário | Lumina">

    <x-slot name="css">
        <style>
            /* Reset forçado para evitar conflitos com layouts antigos */
            body > div.min-h-screen.bg-gray-100 { background: transparent !important; min-height: 0 !important; }
            nav.bg-white.border-b.border-gray-100 { display: none !important; }
            header.bg-white.shadow { display: none !important; }
            
            /* Mood Radios - Animação suave */
            .mood-radio:checked + label { 
                transform: translateY(-2px); 
                border-color: #6366f1; 
                background-color: #f5f3ff; 
                box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15); 
            }
            .mood-radio:checked + label i { color: #4f46e5; }
            .mood-radio:checked + label .radio-indicator { 
                background-color: #4f46e5; 
                border-color: #4f46e5; 
                box-shadow: 0 0 0 2px white inset; 
            }
            
            /* Tags - Estilo Pílula */
            .tag-checkbox:checked + label { 
                background-color: #4f46e5; 
                color: white; 
                border-color: #4f46e5; 
                box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25); 
            }
            
            /* Papel de Carta */
            .journal-paper {
                background-color: transparent;
                background-image: linear-gradient(#e2e8f0 1px, transparent 1px);
                background-size: 100% 2.5rem; 
                line-height: 2.5rem; 
                padding-top: 0.15rem; 
                background-attachment: local;
            }
            
            /* Remove scrollbar feia do textarea */
            textarea { -ms-overflow-style: none; scrollbar-width: none; }
            textarea::-webkit-scrollbar { display: none; }
        </style>
    </x-slot>

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-20 left-10 w-96 h-96 bg-indigo-400/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-teal-400/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-10 pt-32">
        
        <div class="flex flex-col lg:flex-row justify-between items-end gap-8 mb-12 animate-fade-up">
            
            <div class="w-full lg:w-1/2 text-left">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-indigo-600 text-[10px] font-extrabold uppercase tracking-widest shadow-sm mb-4">
                    <i class="ri-lock-2-line"></i> Privado & Seguro
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight">
                    Como está o teu <br class="hidden md:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-500">mundo interior</span> hoje?
                </h1>
                <p class="text-slate-500 text-lg mt-4 max-w-md">
                    Não existem respostas erradas aqui.
                </p>
            </div>

            <div class="w-full lg:w-auto">
                <div class="bg-white/80 backdrop-blur-md p-5 rounded-3xl border border-white shadow-lg shadow-slate-200/50">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Últimos 7 dias</span>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full">{{ $history->count() }} registos</span>
                    </div>
                    <div class="flex gap-2">
                        @for ($i = 6; $i >= 0; $i--)
                            @php 
                                $date = \Carbon\Carbon::today()->subDays($i);
                                $log = $history->firstWhere('log_date', $date);
                                $colors = [
                                    1 => 'bg-rose-500 text-white',    
                                    2 => 'bg-orange-400 text-white',  
                                    3 => 'bg-slate-400 text-white',   
                                    4 => 'bg-teal-400 text-white',    
                                    5 => 'bg-yellow-400 text-white',  
                                ];
                            @endphp
                            <div class="flex flex-col items-center gap-1 group" title="{{ $date->format('d/m') }}">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs transition-all shadow-sm {{ $log ? $colors[$log->mood_level] : 'bg-slate-100 text-slate-300' }}">
                                    @if($log)
                                        @if($log->mood_level == 1) <i class="ri-thunderstorms-fill"></i>
                                        @elseif($log->mood_level == 2) <i class="ri-rainy-fill"></i>
                                        @elseif($log->mood_level == 3) <i class="ri-cloudy-fill"></i>
                                        @elseif($log->mood_level == 4) <i class="ri-sun-cloudy-fill"></i>
                                        @else <i class="ri-sun-fill"></i>
                                        @endif
                                    @else
                                        <div class="w-1.5 h-1.5 bg-slate-300 rounded-full"></div>
                                    @endif
                                </div>
                                <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $date->format('D') }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('diary.store') }}" method="POST">
            @csrf
            
            <div class="grid lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-4 space-y-6 animate-fade-up lg:sticky lg:top-32" style="animation-delay: 0.1s;">
                    
                    <div class="bg-white rounded-[2rem] p-6 shadow-xl shadow-slate-200/40 border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                            <i class="ri-cloud-windy-line text-indigo-500 text-lg"></i> Clima
                        </h3>
                        <div class="space-y-2">
                            @php
                                $moods = [
                                    5 => ['icon' => 'ri-sun-line', 'label' => 'Radiante', 'color' => 'text-yellow-500'],
                                    4 => ['icon' => 'ri-sun-cloudy-line', 'label' => 'Bom', 'color' => 'text-teal-500'],
                                    3 => ['icon' => 'ri-cloudy-line', 'label' => 'Neutro', 'color' => 'text-slate-500'],
                                    2 => ['icon' => 'ri-rainy-line', 'label' => 'Triste', 'color' => 'text-orange-500'],
                                    1 => ['icon' => 'ri-thunderstorms-line', 'label' => 'Difícil', 'color' => 'text-rose-500'],
                                ];
                            @endphp
                            
                            @foreach($moods as $level => $data)
                            <div class="relative">
                                <input type="radio" name="mood_level" id="mood_{{ $level }}" value="{{ $level }}" class="mood-radio sr-only" {{ ($todayLog->mood_level ?? 0) == $level ? 'checked' : '' }} required>
                                <label for="mood_{{ $level }}" class="flex items-center gap-4 p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 cursor-pointer transition-all bg-white">
                                    <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center shrink-0">
                                        <i class="{{ $data['icon'] }} text-xl {{ $data['color'] }}"></i>
                                    </div>
                                    <span class="font-bold text-slate-600 text-sm flex-1">{{ $data['label'] }}</span>
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-200 radio-indicator flex items-center justify-center transition-all"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] p-6 shadow-xl shadow-slate-200/40 border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                            <i class="ri-price-tag-3-line text-indigo-500 text-lg"></i> Emoções
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @php 
                                $tags = ['Ansiedade', 'Cansaço', 'Stress', 'Solidão', 'Foco', 'Gratidão', 'Esperança', 'Energia', 'Calma', 'Insónia', 'Orgulho', 'Nostalgia']; 
                                $currentTags = $todayLog ? $todayLog->tags : [];
                            @endphp

                            @foreach($tags as $tag)
                                <div class="relative">
                                    <input type="checkbox" name="tags[]" id="tag_{{ $tag }}" value="{{ $tag }}" class="tag-checkbox sr-only" {{ in_array($tag, $currentTags ?? []) ? 'checked' : '' }}>
                                    <label for="tag_{{ $tag }}" class="inline-block px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 text-xs font-bold cursor-pointer hover:bg-slate-50 transition-all select-none">
                                        {{ $tag }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-slate-200/50 border border-slate-100 min-h-[600px] flex flex-col relative overflow-hidden">
                        
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-6 border-b border-slate-100 gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                                    <i class="ri-quill-pen-line text-indigo-500"></i> Diário de Bordo
                                </h2>
                                <p class="text-sm text-slate-400 mt-1 font-medium">{{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM [de] YYYY') }}</p>
                            </div>
                            
                            <button type="button" onclick="generatePrompt()" class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors flex items-center gap-2 border border-indigo-100 group">
                                <i class="ri-magic-line text-lg group-hover:rotate-12 transition-transform"></i> Sugerir tema
                            </button>
                        </div>

                        <div id="prompt-box" class="hidden mb-6 bg-amber-50 border border-amber-100 p-5 rounded-2xl text-amber-900 text-base italic relative animate-fade-up">
                            <span class="font-serif text-3xl text-amber-300 absolute -top-2 left-3">"</span>
                            <span id="prompt-text" class="block px-6 relative z-10 font-medium"></span>
                            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="absolute top-3 right-3 text-amber-400 hover:text-amber-600 w-6 h-6 flex items-center justify-center rounded-full hover:bg-amber-100 transition-colors"><i class="ri-close-line"></i></button>
                        </div>

                        <div class="flex-1 relative">
                            <textarea name="note" id="journal-area"
                                class="journal-paper w-full h-full min-h-[400px] bg-transparent border-none focus:ring-0 text-slate-700 text-lg resize-none p-0 placeholder:text-slate-300 placeholder:italic"
                                placeholder="Clica para começar a escrever...">{{ $todayLog->note ?? '' }}</textarea>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <p class="text-xs text-slate-400 flex items-center gap-1.5 select-none">
                                <i class="ri-lock-2-line"></i> 100% Privado
                            </p>
                            
                            <button type="submit" class="w-full sm:w-auto !bg-slate-900 hover:!bg-slate-800 !text-white font-bold py-4 px-10 rounded-2xl shadow-xl shadow-slate-900/20 transform transition hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                                <span>Guardar Memória</span> 
                                <i class="ri-save-line text-lg"></i>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </form>
        @if(isset($todayLog) && $todayLog->cbt_insight)
            @php $insight = json_decode($todayLog->cbt_insight, true); @endphp
            
            <div class="mt-8 bg-indigo-50 border border-indigo-100 rounded-3xl p-6 md:p-8 relative overflow-hidden animate-fade-up">
                <div class="absolute -top-6 -right-6 text-9xl text-indigo-500/10"><i class="ri-brain-line"></i></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-3 py-1 bg-indigo-200 text-indigo-700 text-xs font-bold rounded-full uppercase tracking-wider">
                            Lumina Insight
                        </span>
                        <span class="text-xs text-slate-500 font-medium">Reflexão Guiada</span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $insight['message'] }}</h3>
                    <p class="text-sm text-slate-600 mb-6 border-l-2 border-indigo-300 pl-3">Aviso: Isto é um exercício de reflexão (Terapia Cognitivo-Comportamental), não substitui aconselhamento médico profissional.</p>
                    
                    <div class="space-y-3">
                        <p class="font-bold text-slate-700 text-sm">Responde a ti próprio mentalmente ou num papel:</p>
                        <ul class="space-y-2">
                            @foreach($insight['prompts'] as $index => $prompt)
                                <li class="bg-white p-4 rounded-xl shadow-sm text-slate-700 text-sm flex gap-3 items-start border border-slate-100">
                                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">{{ $index + 1 }}</span>
                                    {{ $prompt }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        const prompts = [
            "Qual foi a melhor coisa (mesmo que pequena) que aconteceu hoje?",
            "O que é que está a ocupar demasiado espaço na tua cabeça agora?",
            "Escreve sobre um momento em que te sentiste em paz hoje.",
            "O que é que te deixou ansioso hoje e como lidaste com isso?",
            "Pelo que é que te sentes grato(a) neste momento?",
            "O que precisas de perdoar a ti mesmo hoje?"
        ];

        function generatePrompt() {
            const box = document.getElementById('prompt-box');
            const text = document.getElementById('prompt-text');
            const randomPrompt = prompts[Math.floor(Math.random() * prompts.length)];
            text.textContent = randomPrompt;
            box.classList.remove('hidden');
        }
    </script>

</x-lumina-layout>