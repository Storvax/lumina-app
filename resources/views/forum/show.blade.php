<x-lumina-layout :title="$post->title . ' | Lumina'">

    @php
        $colors = match($post->tag) {
            'hope' => 'from-emerald-500 to-teal-400',
            'vent' => 'from-rose-500 to-pink-500',
            'anxiety' => 'from-amber-400 to-orange-500',
            default => 'from-indigo-500 to-blue-500'
        };
        $bgTheme = match($post->tag) {
            'hope' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'vent' => 'bg-rose-50 text-rose-700 border-rose-100',
            'anxiety' => 'bg-amber-50 text-amber-700 border-amber-100',
            default => 'bg-indigo-50 text-indigo-700 border-indigo-100'
        };
        $icon = match($post->tag) {
            'hope' => 'ri-seedling-fill',
            'vent' => 'ri-heart-pulse-fill',
            'anxiety' => 'ri-flashlight-fill',
            default => 'ri-chat-smile-fill'
        };
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8 animate-fade-up">
            <a href="{{ route('forum.index') }}" class="hover:text-indigo-600 transition-colors"><i class="ri-arrow-left-line"></i> Voltar ao Mural</a>
            <span>/</span>
            <span class="font-medium text-slate-600">{{ ucfirst($post->tag) }}</span>
        </nav>

        <div class="grid lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 space-y-8">
                
                <div class="fixed top-0 left-0 w-full h-1 z-[60]">
                    <div id="reading-progress" class="h-full bg-gradient-to-r {{ $colors }} w-0 transition-all duration-100 ease-out"></div>
                </div>

                <article class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-hidden border border-white/50 relative animate-fade-up isolate">
                    
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-gradient-to-br {{ $colors }} opacity-10 rounded-full blur-3xl -z-10"></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r {{ $colors }} opacity-20"></div>

                    <div class="h-2 w-full bg-gradient-to-r {{ $colors }}"></div>

                    <div class="p-8 md:p-12">
                        <div class="flex items-center justify-between mb-10 border-b border-slate-100 pb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-white border-2 border-slate-50 text-slate-600 flex items-center justify-center font-bold text-2xl uppercase shadow-sm">
                                    {{ substr($post->user->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-lg">{{ $post->user->name ?? 'Membro Lumina' }}</p>
                                    <p class="text-xs text-slate-400 font-medium flex items-center gap-2 mt-1">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-500">Autor</span>
                                        <span>•</span>
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="hidden sm:flex flex-col items-end">
                                <div class="flex items-center gap-2 px-4 py-1.5 rounded-full border {{ $bgTheme }} mb-1">
                                    <i class="{{ $icon }}"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">{{ $post->tag }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-medium">Leitura: ~2 min</span>
                            </div>
                        </div>

                        <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-8 leading-[1.15] tracking-tight">
                            {{ $post->title }}
                        </h1>
                        
                        <div class="prose prose-lg prose-slate max-w-none text-slate-600 leading-relaxed 
                                    first-letter:text-7xl first-letter:font-bold first-letter:text-slate-900 first-letter:mr-3 first-letter:float-left first-letter:leading-[0.8]">
                            {!! nl2br(e($post->content)) !!}
                        </div>

                        <div class="my-10 p-6 bg-slate-50 rounded-2xl border-l-4 border-indigo-400 italic text-slate-600 text-lg relative">
                            <i class="ri-double-quotes-l absolute top-4 left-4 text-4xl text-indigo-100 -z-10"></i>
                            "Lembra-te: O progresso não é linear. O que sentes hoje não define quem és amanhã."
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 text-center">Como esta história te fez sentir?</p>
                            <div class="flex justify-center flex-wrap items-center gap-4">
                                <button onclick="react({{ $post->id }}, 'hug', this)" class="group relative flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-white border-2 border-slate-100 hover:border-rose-400 hover:bg-rose-50 transition-all duration-300">
                                    <span class="text-3xl mb-1 group-hover:scale-110 transition-transform">🫂</span>
                                    <span class="text-xs font-bold text-slate-400 group-hover:text-rose-600 count-hug">{{ $post->reactions->where('type', 'hug')->count() }}</span>
                                </button>

                                <button onclick="react({{ $post->id }}, 'candle', this)" class="group relative flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-white border-2 border-slate-100 hover:border-amber-400 hover:bg-amber-50 transition-all duration-300">
                                    <span class="text-3xl mb-1 group-hover:scale-110 transition-transform">🕯️</span>
                                    <span class="text-xs font-bold text-slate-400 group-hover:text-amber-600 count-candle">{{ $post->reactions->where('type', 'candle')->count() }}</span>
                                </button>

                                <button onclick="react({{ $post->id }}, 'ear', this)" class="group relative flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-white border-2 border-slate-100 hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-300">
                                    <span class="text-3xl mb-1 group-hover:scale-110 transition-transform">👂</span>
                                    <span class="text-xs font-bold text-slate-400 group-hover:text-indigo-600 count-ear">{{ $post->reactions->where('type', 'ear')->count() }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>

                <script>
                    window.addEventListener('scroll', () => {
                        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                        const scrolled = (winScroll / height) * 100;
                        document.getElementById("reading-progress").style.width = scrolled + "%";
                    });
                </script>
                <div class="bg-slate-50 rounded-[2rem] border border-slate-200 p-6 md:p-8 animate-fade-up" style="animation-delay: 0.1s;">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="ri-discuss-line text-indigo-500"></i> Conversa de Apoio <span class="bg-indigo-100 text-indigo-600 text-xs px-2 py-1 rounded-full">{{ $post->comments->count() }}</span>
                    </h3>

                    <form action="{{ route('forum.comment', $post) }}" method="POST" class="group relative mb-10">
                        @csrf
                        <div class="absolute left-4 top-4 w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md z-10">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <textarea name="body" rows="3" placeholder="Escreve uma mensagem de apoio..." class="w-full pl-16 pr-4 py-4 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 resize-none transition-all shadow-sm group-focus-within:shadow-md"></textarea>
                        <div class="absolute bottom-3 right-3">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg shadow-indigo-200 transition-transform active:scale-95 flex items-center gap-2">
                                <span>Enviar</span> <i class="ri-send-plane-fill"></i>
                            </button>
                        </div>
                    </form>

                    <div class="space-y-6">
                        @forelse($post->comments as $comment)
                            <div class="flex gap-4 group">
                                <div class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="bg-white p-5 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm group-hover:shadow-md transition-shadow relative">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="font-bold text-slate-800 text-sm">{{ $comment->user->name }}</span>
                                            <span class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-slate-600 text-sm leading-relaxed">{{ $comment->body }}</p>
                                    </div>
                                    <button class="text-xs font-bold text-slate-400 hover:text-rose-500 mt-2 ml-2 flex items-center gap-1 transition-colors">
                                        <i class="ri-heart-line"></i> Útil
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 opacity-50">
                                <i class="ri-chat-voice-line text-4xl text-slate-300 mb-2 block"></i>
                                <p class="text-sm text-slate-500">Sê a primeira voz amiga a comentar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 animate-fade-up" style="animation-delay: 0.2s;">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Sobre o Autor</h4>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br {{ $colors }} p-1">
                            <div class="w-full h-full bg-white rounded-full flex items-center justify-center text-xl font-bold text-slate-700">
                                {{ substr($post->user->name, 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-lg">{{ $post->user->name }}</p>
                            <p class="text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded inline-block mt-1">Membro Ativo</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center border-t border-slate-100 pt-4">
                        <div>
                            <span class="block font-bold text-slate-800 text-lg">{{ $post->user->posts->count() }}</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Posts</span>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-lg">12</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Apoios</span>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 text-lg">5</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Dias</span>
                        </div>
                    </div>
                </div>

                @if($relatedPosts->count() > 0)
                <div class="bg-white/60 backdrop-blur-sm rounded-3xl p-6 border border-slate-200 animate-fade-up" style="animation-delay: 0.3s;">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Outras Histórias</h4>
                    <div class="space-y-4">
                        @foreach($relatedPosts as $related)
                            <a href="{{ route('forum.show', $related) }}" class="block group">
                                <h5 class="font-bold text-slate-700 text-sm group-hover:text-indigo-600 transition-colors leading-tight mb-1">{{ $related->title }}</h5>
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <span>{{ $related->created_at->diffForHumans() }}</span>
                                    <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                    <span class="group-hover:translate-x-1 transition-transform">Ler <i class="ri-arrow-right-line"></i></span>
                                </div>
                            </a>
                            @if(!$loop->last) <hr class="border-slate-100"> @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-6 text-white text-center shadow-xl shadow-indigo-200 relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
                    
                    <div class="relative z-10" id="breathe-widget">
                        <div class="flex justify-between items-start mb-4">
                            <i class="ri-lungs-line text-2xl opacity-80"></i>
                            <span class="text-[10px] bg-white/20 px-2 py-1 rounded-full uppercase tracking-wider font-bold">Calma</span>
                        </div>

                        <div class="relative w-32 h-32 mx-auto mb-6 flex items-center justify-center">
                            <div id="breathe-ring-1" class="absolute inset-0 border-4 border-white/10 rounded-full transition-all duration-[4000ms]"></div>
                            <div id="breathe-ring-2" class="absolute inset-4 border-4 border-white/20 rounded-full transition-all duration-[4000ms]"></div>
                            
                            <div id="breathe-circle" class="relative z-10 w-16 h-16 bg-white text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg shadow-2xl transition-all duration-[4000ms] cursor-pointer hover:scale-105" onclick="toggleBreathing()">
                                <i class="ri-play-fill text-2xl" id="breathe-icon"></i>
                                <span id="breathe-text" class="hidden text-xs">4s</span>
                            </div>
                        </div>
                        
                        <h4 id="breathe-instruction" class="font-bold text-lg mb-1">Precisas de uma pausa?</h4>
                        <p id="breathe-sub" class="text-xs text-indigo-200">Clica no círculo para começar.</p>
                    </div>

                    <script>
                        let breatheInterval;
                        let isBreathing = false;
                        
                        function toggleBreathing() {
                            const circle = document.getElementById('breathe-circle');
                            const ring1 = document.getElementById('breathe-ring-1');
                            const ring2 = document.getElementById('breathe-ring-2');
                            const icon = document.getElementById('breathe-icon');
                            const textSpan = document.getElementById('breathe-text');
                            const title = document.getElementById('breathe-instruction');
                            const sub = document.getElementById('breathe-sub');

                            if (isBreathing) {
                                // PARAR
                                clearInterval(breatheInterval);
                                isBreathing = false;
                                
                                // Reset Visuals
                                icon.classList.remove('hidden');
                                textSpan.classList.add('hidden');
                                circle.style.transform = 'scale(1)';
                                ring1.style.transform = 'scale(1)';
                                title.innerText = "Pausa terminada";
                                sub.innerText = "Clica para recomeçar";
                                
                                setTimeout(() => { title.innerText = "Precisas de uma pausa?"; }, 2000);
                                
                            } else {
                                // COMEÇAR
                                isBreathing = true;
                                icon.classList.add('hidden');
                                textSpan.classList.remove('hidden');
                                
                                let phase = 0; // 0: Inhale, 1: Hold, 2: Exhale
                                
                                function runPhase() {
                                    if(!isBreathing) return;

                                    if (phase === 0) {
                                        // INSPIRA (4s)
                                        title.innerText = "Inspira...";
                                        sub.innerText = "Enche os pulmões devagar";
                                        textSpan.innerText = "Inspira";
                                        circle.style.transform = 'scale(1.5)';
                                        ring1.style.transform = 'scale(1.8)';
                                        ring1.style.borderColor = 'rgba(255,255,255,0.5)';
                                        phase = 1;
                                    } else if (phase === 1) {
                                        // SEGURA (4s)
                                        title.innerText = "Segura...";
                                        sub.innerText = "Mantém o ar";
                                        textSpan.innerText = "Segura";
                                        // Mantém tamanho
                                        phase = 2;
                                    } else {
                                        // EXPIRA (4s)
                                        title.innerText = "Expira...";
                                        sub.innerText = "Deita tudo cá para fora";
                                        textSpan.innerText = "Expira";
                                        circle.style.transform = 'scale(1)';
                                        ring1.style.transform = 'scale(1)';
                                        ring1.style.borderColor = 'rgba(255,255,255,0.1)';
                                        phase = 0;
                                    }
                                }

                                runPhase(); // Executa imediato
                                breatheInterval = setInterval(runPhase, 4000); // Repete a cada 4s
                            }
                        }
                    </script>
                </div>

            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            window.react = async function(postId, type, btn) {
                btn.classList.add('scale-110'); 
                setTimeout(() => btn.classList.remove('scale-110'), 200);
                
                const countSpan = btn.querySelector(`span[class*="count-"]`);
                let current = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = current + 1; 
                
                try { 
                    await axios.post(`/mural/${postId}/reagir`, { type: type }); 
                } catch (e) { 
                    countSpan.textContent = current; 
                }
            };
        </script>
    </x-slot>

</x-lumina-layout>