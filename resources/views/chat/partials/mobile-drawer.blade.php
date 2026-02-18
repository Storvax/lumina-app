<aside id="mobile-drawer" class="fixed inset-y-0 right-0 w-full max-w-[300px] bg-white shadow-2xl z-[90] closed lg:hidden flex flex-col p-6 overflow-y-auto border-l border-slate-100">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Menu Lumina</h2>
        <button onclick="toggleMobileMenu()" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"><i class="ri-close-line text-xl"></i></button>
    </div>

    @if(Auth::user()->isModerator())
    <div class="mb-6 bg-slate-900 text-white rounded-2xl p-4 border border-slate-700">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-xs uppercase tracking-widest text-slate-400">Moderação</h3>
            <span class="bg-rose-600 text-[10px] px-2 py-0.5 rounded font-bold">Admin</span>
        </div>
        <button onclick="toggleCrisisMode()" id="crisis-btn-mobile" class="w-full {{ $room->is_crisis_mode ? 'bg-rose-600 animate-pulse' : 'bg-slate-800' }} text-white font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
            <i class="ri-alarm-warning-fill"></i> 
            <span>{{ $room->is_crisis_mode ? 'DESATIVAR CRISE' : 'MODO CRISE' }}</span>
        </button>
    </div>
    @endif

    <div class="mb-6 bg-slate-50 rounded-2xl p-4 border border-slate-100">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Pessoas Aqui <span id="mobile-drawer-counter" class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-md font-bold text-[10px]">0</span></h3>
        <ul id="users-list-mobile" class="space-y-3 max-h-40 overflow-y-auto pr-1"></ul>
    </div>
    
    <div class="bg-indigo-900 rounded-3xl p-5 text-white relative overflow-hidden shadow-xl mb-6 ring-1 ring-white/20">
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-violet-900"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div><h3 class="font-bold text-lg">Sons de Calma</h3><p id="now-playing-text-mobile" class="text-[10px] text-indigo-200 h-4">Toque para ouvir</p></div><i class="ri-volume-up-line text-indigo-300"></i>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button id="btn-rain-mobile" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 active:bg-white/20 transition-all border border-white/5" onclick="toggleSound('rain')"><i class="ri-rainy-line text-xl"></i> <span class="text-[10px] font-medium">Chuva</span></button>
                <button id="btn-fire-mobile" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 active:bg-white/20 transition-all border border-white/5" onclick="toggleSound('fire')"><i class="ri-fire-line text-xl"></i> <span class="text-[10px] font-medium">Fogo</span></button>
                <button id="btn-forest-mobile" class="sound-btn flex flex-col items-center gap-2 p-3 rounded-2xl bg-white/10 active:bg-white/20 transition-all border border-white/5" onclick="toggleSound('forest')"><i class="ri-tree-line text-xl"></i> <span class="text-[10px] font-medium">Mata</span></button>
            </div>
        </div>
    </div>
    <div class="mb-6 flex-1">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 px-1">Mudar de Sala</h3>
        <div class="grid grid-cols-4 gap-3">
            @foreach($allRooms as $r)
            <a href="{{ route('chat.show', $r) }}" class="aspect-square rounded-2xl flex items-center justify-center transition-all {{ $r->id == $room->id ? 'bg-'.$r->color.'-100 text-'.$r->color.'-600 ring-2 ring-'.$r->color.'-200' : 'bg-slate-50 text-slate-400 border border-slate-100' }}"><i class="{{ $r->icon }} text-xl"></i></a>
            @endforeach
        </div>
    </div>
     <a href="{{ url('/') }}" class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 text-slate-600 font-bold mt-auto"><i class="ri-arrow-left-circle-line text-xl"></i> Voltar ao Início</a>
</aside>