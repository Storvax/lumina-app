<aside class="hidden xl:flex flex-col w-80 bg-white border-l border-slate-200 p-6 gap-6 z-20 shrink-0 overflow-y-auto">
    
    @if(Auth::user()->isModerator())
    <div class="bg-slate-900 rounded-2xl p-4 text-white shadow-lg border border-slate-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2"><i class="ri-shield-star-line text-amber-400"></i> Moderação</h3>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-4">
            <div class="bg-slate-800/50 rounded-lg p-2 text-center"><span class="block text-lg font-bold">{{ $modStats['messages_24h'] ?? 0 }}</span><span class="text-[9px] text-slate-400 uppercase">Msgs (24h)</span></div>
            <div class="bg-slate-800/50 rounded-lg p-2 text-center"><span class="block text-lg font-bold text-rose-400">{{ $modStats['pending_reports'] ?? 0 }}</span><span class="text-[9px] text-slate-400 uppercase">Reports</span></div>
        </div>
        <button onclick="toggleCrisisMode()" id="crisis-btn" class="w-full {{ $room->is_crisis_mode ? 'bg-rose-600 animate-pulse' : 'bg-slate-800 hover:bg-slate-700' }} text-white font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-2 mb-4 transition-all border border-white/10">
            <i class="ri-alarm-warning-fill"></i> <span id="crisis-btn-text">{{ $room->is_crisis_mode ? 'DESATIVAR MODO CRISE' : 'ATIVAR MODO CRISE' }}</span>
        </button>
        <div class="border-t border-slate-700 pt-3">
            <h4 class="text-[10px] font-bold text-slate-500 mb-2">Logs Recentes</h4>
            <div class="space-y-2 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                @foreach($modLogs ?? [] as $log)
                <div class="text-[10px] text-slate-400 flex flex-col bg-slate-800/30 p-1.5 rounded">
                    <div class="flex justify-between"><span class="font-bold text-slate-300">{{ $log->moderator_name }}</span><span class="text-[9px] opacity-60">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span></div>
                    <span class="text-amber-500/80">{{ $log->action }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="bg-indigo-900 rounded-3xl p-5 text-white relative overflow-hidden shadow-xl group ring-4 ring-slate-50">
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-purple-800"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div><h3 class="font-bold text-lg">Sons de Calma</h3><p id="now-playing-text" class="text-[10px] text-indigo-200 h-4 mt-0.5">Pausa para relaxar</p></div><i class="ri-volume-up-line text-indigo-300"></i>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button id="btn-rain" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/5" onclick="toggleSound('rain')"><i class="ri-rainy-line text-xl"></i> <span class="text-[10px] font-medium">Chuva</span></button>
                <button id="btn-fire" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/5" onclick="toggleSound('fire')"><i class="ri-fire-line text-xl"></i> <span class="text-[10px] font-medium">Lareira</span></button>
                <button id="btn-forest" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/5" onclick="toggleSound('forest')"><i class="ri-tree-line text-xl"></i> <span class="text-[10px] font-medium">Mata</span></button>
            </div>
        </div>
    </div>

    <div class="flex-1">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center justify-between"><span>Na Fogueira</span><span id="desktop-counter" class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md font-bold text-xs">0</span></h3>
        <ul id="online-users-list" class="space-y-3"></ul>
    </div>

    <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100/60">
        <h4 class="font-bold text-amber-800 text-sm mb-2 flex items-center gap-2"><i class="ri-anchor-line"></i> Grounding</h4>
        <p class="text-xs text-amber-700/70 leading-relaxed font-medium">Se sentires ansiedade:<br>👀 5 coisas que vês<br>✋ 4 coisas que tocas<br>👂 3 coisas que ouves<br>👃 2 coisas que cheiras<br>👅 1 coisa que saboreias</p>
    </div>
</aside>