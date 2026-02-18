<header class="glass-panel px-4 py-3 flex flex-col gap-2 z-10 sticky top-0 shrink-0">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-3 overflow-hidden">
            <a href="{{ route('rooms.index') }}" class="lg:hidden w-9 h-9 rounded-full bg-slate-100 active:bg-slate-200 flex items-center justify-center text-slate-500 shrink-0"><i class="ri-arrow-left-line"></i></a>
            <div class="min-w-0 flex flex-col justify-center">
                <div class="flex items-center gap-2">
                    <h1 class="text-base md:text-xl font-bold text-slate-900 truncate leading-tight">{{ $room->name }}</h1>
                    <span class="hidden md:flex bg-{{ $room->color }}-100 text-{{ $room->color }}-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md border border-{{ $room->color }}-200 items-center gap-1 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-{{ $room->color }}-500 animate-pulse"></span> LIVE</span>
                </div>
                <p class="text-xs text-slate-500 hidden md:block truncate">{{ $room->description }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2 pl-2 shrink-0">
            <button onclick="toggleViewMode()" id="view-mode-btn" class="w-8 h-8 rounded-full flex items-center justify-center border transition-all {{ Auth::user()->chat_view_mode === 'compact' ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300' }}" title="Alternar Densidade (Compacto/Confortável)">
                <i class="{{ Auth::user()->chat_view_mode === 'compact' ? 'ri-list-check-2' : 'ri-list-unordered' }}"></i>
            </button>

            <button id="dnd-btn" onclick="toggleDnd()" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-slate-200 text-slate-500 hover:border-indigo-200 hover:text-indigo-600 transition-all text-xs font-bold group" title="Pausar Chat">
                <i class="ri-pause-circle-line text-lg group-hover:scale-110 transition-transform"></i>
                <span class="hidden sm:inline" id="dnd-text">Pausa</span>
            </button>
            
            <div class="flex items-center gap-1.5 bg-slate-100/80 px-2.5 py-1.5 rounded-full border border-slate-200/50">
                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
                <span id="mobile-counter" class="font-bold text-xs text-slate-700 tabular-nums">0</span>
            </div>
            <button onclick="toggleMobileMenu()" class="w-9 h-9 md:hidden rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-200 flex items-center justify-center active:scale-95 transition-transform"><i class="ri-menu-4-line text-lg"></i></button>
        </div>
    </div>

    @if($room->pinned_message || Auth::user()->isModerator())
        @endif
</header>