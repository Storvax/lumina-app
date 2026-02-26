<x-lumina-layout title="A Fogueira | Comunidade Lumina">

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none bg-[#F8FAFC] dark:bg-slate-900 transition-colors duration-500">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-orange-300/20 dark:bg-orange-900/20 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-lighten animate-pulse" style="animation-duration: 8s;"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[600px] h-[600px] bg-rose-300/20 dark:bg-rose-900/20 rounded-full blur-[120px] mix-blend-multiply dark:mix-blend-lighten animate-pulse" style="animation-duration: 10s;"></div>
    </div>

    <div class="pt-12 pb-24 min-h-screen relative z-10"
         x-data="liveRooms(@json($rooms->mapWithKeys(fn($r) => [(string) $r->id => $initialStats->get($r->id, 0)])))"
         x-init="initPolling()">
        
        <div class="max-w-7xl mx-auto px-6 mb-16 text-center animate-fade-up">
            <div class="inline-flex items-center gap-2 py-1 px-3 rounded-full bg-white/80 dark:bg-slate-800/80 border border-orange-100 dark:border-orange-900/50 shadow-sm backdrop-blur-md mb-6">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                </span>
                <span class="text-orange-600 dark:text-orange-400 text-[10px] font-bold uppercase tracking-widest">Salas ao Vivo</span>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 dark:text-white mb-6 tracking-tight">
                Escolhe a tua <span class="bg-clip-text text-transparent bg-gradient-to-r from-orange-500 to-rose-500 drop-shadow-sm">Fogueira</span>
            </h1>
            <p class="text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Estes são espaços seguros, anónimos e moderados. Entra, senta-te à roda, ouve o que os outros têm a dizer e partilha quando te sentires pronto.
            </p>
        </div>

        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                @forelse($rooms as $room)
                    @php
                        $color = in_array($room->color, ['rose', 'emerald', 'blue', 'amber', 'indigo', 'orange', 'teal', 'violet']) ? $room->color : 'indigo';
                        $icon = !empty($room->icon) && str_contains($room->icon, 'ri-') ? $room->icon : match($color) {
                            'rose' => 'ri-heart-pulse-fill',
                            'emerald' => 'ri-leaf-fill',
                            'blue' => 'ri-drop-fill',
                            'amber' => 'ri-flashlight-fill',
                            'orange' => 'ri-fire-fill',
                            'teal' => 'ri-windy-fill',
                            'violet' => 'ri-moon-clear-fill',
                            default => 'ri-discuss-fill'
                        };
                        $seed = md5($room->id . $room->name); 
                    @endphp

                    <a href="{{ route('chat.show', $room) }}" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-[2rem] p-8 border border-white dark:border-slate-700 shadow-lg shadow-slate-200/40 dark:shadow-none hover:shadow-2xl hover:shadow-slate-300 transition-all duration-500 hover:-translate-y-2 flex flex-col h-full isolate focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-{{ $color }}-500">
                        
                        <div class="absolute inset-0 bg-gradient-to-br from-{{ $color }}-50/80 to-transparent dark:from-{{ $color }}-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2rem] -z-10"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-{{ $color }}-100 to-white/0 dark:from-{{ $color }}-900/30 dark:to-transparent rounded-tr-[2rem] rounded-bl-[100px] transition-transform duration-700 group-hover:scale-110 -z-10"></div>
                        
                        <div class="relative z-10 flex-grow">
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-16 h-16 rounded-2xl bg-white border border-slate-100 text-{{ $color }}-500 flex items-center justify-center text-3xl shadow-sm group-hover:scale-110 group-hover:rotate-6 group-hover:border-{{ $color }}-200 transition-transform duration-500">
                                    <i class="{{ $icon }}"></i>
                                </div>
                                
                                <template x-if="stats['{{ $room->id }}'] > 0">
                                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Ativa
                                    </div>
                                </template>
                                <template x-if="stats['{{ $room->id }}'] === 0">
                                    <div class="bg-slate-50 border border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                                        Livre
                                    </div>
                                </template>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-3 group-hover:text-{{ $color }}-600 dark:group-hover:text-{{ $color }}-400 transition-colors">{{ $room->name }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-6 line-clamp-3">{{ $room->description }}</p>
                        </div>

                        <div class="relative z-10 mt-auto pt-5 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                            
                            <div class="flex items-center -space-x-3 group-hover:space-x-[-8px] transition-all duration-300">
                                <template x-if="stats['{{ $room->id }}'] > 0">
                                    <div class="flex items-center -space-x-3 group-hover:space-x-[-8px] transition-all duration-300">
                                        <img src="https://api.dicebear.com/7.x/notionists/svg?seed={{ $seed }}1&backgroundColor=f8fafc" alt="Membro" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-800 shadow-sm relative z-30">
                                        
                                        <template x-if="stats['{{ $room->id }}'] > 1">
                                            <img src="https://api.dicebear.com/7.x/notionists/svg?seed={{ $seed }}2&backgroundColor=f8fafc" alt="Membro" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-800 shadow-sm relative z-20">
                                        </template>
                                        
                                        <div class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-800 bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 shadow-sm relative z-10" x-text="'+' + stats['{{ $room->id }}']"></div>
                                    </div>
                                </template>
                                
                                <template x-if="stats['{{ $room->id }}'] === 0">
                                    <div class="h-10 px-4 rounded-full border-2 border-white dark:border-slate-800 bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-400 shadow-sm relative z-10">
                                        Sê o primeiro a entrar
                                    </div>
                                </template>
                            </div>
                            
                            <div class="w-12 h-12 rounded-full border border-slate-200 bg-white text-slate-400 flex items-center justify-center group-hover:bg-slate-900 group-hover:border-slate-900 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-xl">
                                <i class="ri-arrow-right-line text-xl group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-20 text-center bg-white/50 dark:bg-slate-800/50 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-4xl text-slate-300 dark:text-slate-600 mx-auto mb-6"><i class="ri-tent-line"></i></div>
                        <h3 class="text-2xl font-bold text-slate-700 dark:text-slate-300 mb-2">O acampamento está a ser montado</h3>
                        <p class="text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Ainda não existem salas de apoio disponíveis neste momento. A nossa equipa está a preparar as fogueiras.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('liveRooms', (initialStats) => ({
                stats: initialStats,
                initPolling() {
                    setInterval(async () => {
                        try {
                            const response = await axios.get('{{ route("rooms.index") }}', {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            // Atualiza os contadores mágicamente na UI
                            this.stats = response.data;
                        } catch (error) {
                            console.error('Falha silenciosa ao atualizar utilizadores ativos.');
                        }
                    }, 15000); // 15 segundos
                }
            }));
        });
    </script>

    <a href="{{ config('app.quick_exit_url', 'https://www.google.pt') }}" class="fixed bottom-6 right-6 z-[60] bg-rose-600 hover:bg-rose-700 text-white font-bold py-3.5 px-6 rounded-full shadow-xl shadow-rose-600/20 flex items-center gap-2 transition-transform hover:scale-105 border-[3px] border-white dark:border-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-rose-500" title="Sair rapidamente" target="_blank">
        <i class="ri-eye-off-line text-xl"></i> <span class="hidden md:inline">Saída Rápida (Esc 2x)</span>
    </a>

</x-lumina-layout>