<x-lumina-layout title="Painel de Ouvinte | Lumina">
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                        <i class="ri-headphone-line text-teal-500"></i> Painel do Ouvinte
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Obrigado por doares o teu tempo para ouvir os outros.</p>
                </div>
                
                <div class="flex items-center gap-3 bg-white dark:bg-slate-800 px-4 py-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <span class="text-sm font-bold {{ Auth::user()->is_buddy_available ? 'text-green-500' : 'text-slate-400' }}">
                        {{ Auth::user()->is_buddy_available ? 'Estou Online' : 'Estou Offline' }}
                    </span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" {{ Auth::user()->is_buddy_available ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 flex items-center gap-4">
                    <div class="w-14 h-14 bg-teal-50 dark:bg-teal-900/30 text-teal-500 rounded-2xl flex items-center justify-center text-2xl"><i class="ri-heart-pulse-fill"></i></div>
                    <div>
                        <p class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['total_helped'] }}</p>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Vidas Tocadas</p>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 flex items-center gap-4">
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/30 text-amber-500 rounded-2xl flex items-center justify-center text-2xl"><i class="ri-star-smile-fill"></i></div>
                    <div>
                        <p class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['avg_rating'], 1) }}/3</p>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Feedback Médio</p>
                    </div>
                </div>
                
                <a href="#" class="bg-indigo-600 rounded-3xl p-6 text-white hover:bg-indigo-700 transition-colors flex items-center justify-between group">
                    <div>
                        <h3 class="font-bold text-lg">Manual do Ouvinte</h3>
                        <p class="text-indigo-200 text-sm">Ver regras e CBT.</p>
                    </div>
                    <i class="ri-book-read-line text-3xl opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all"></i>
                </a>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <div>
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center gap-2"><i class="ri-alarm-warning-line text-rose-500"></i> Pedidos a Aguardar</h2>
                    <div class="space-y-4">
                        @forelse($pendingRequests as $request)
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white text-sm">Pedido Anónimo #{{ $request->id }}</p>
                                    <p class="text-xs text-slate-500 mt-1"><i class="ri-time-line"></i> Espera há {{ $request->created_at->diffInMinutes() }} minutos</p>
                                </div>
                                <form action="{{ route('buddy.accept', $request) }}" method="POST">
                                    @csrf
                                    <button class="bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors">Aceitar Chamada</button>
                                </form>
                            </div>
                        @empty
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-8 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 text-center">
                                <p class="text-slate-500">Nenhum pedido pendente de momento. A comunidade está tranquila.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center gap-2"><i class="ri-message-3-line text-indigo-500"></i> As Minhas Conversas</h2>
                    <div class="space-y-4">
                        @forelse($activeSessions as $session)
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white text-sm">Sessão #{{ $session->id }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold uppercase rounded-md">Ativa</span>
                                </div>
                                
                                <div class="flex gap-2">
                                    <a href="{{ route('chat.show', $session->room->slug) }}" class="flex-1 text-center bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold px-4 py-2 rounded-xl text-sm transition-colors">Ir para Chat</a>
                                    
                                    <form action="{{ route('buddy.escalate', $session) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Tens a certeza que queres escalar esta conversa para emergência clínica? O utilizador e moderadores serão notificados.')">
                                        @csrf
                                        <button class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-500 hover:text-white p-2 rounded-xl text-sm transition-colors" title="Pedir ajuda a moderador clínico">
                                            <i class="ri-alarm-warning-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-lumina-layout>