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
                <p id="now-playing-header" class="text-[10px] text-indigo-500 font-bold md:hidden hidden animate-pulse truncate">🎵 A tocar: Chuva</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 pl-2 shrink-0">
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
        <div class="bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-2 flex items-start gap-2 text-xs text-indigo-800">
            <i class="ri-pushpin-fill text-indigo-500 mt-0.5"></i>
            <div class="flex-1">
                @if(Auth::user()->isModerator())
                    <form action="{{ route('chat.pin', $room) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="message" value="{{ $room->pinned_message }}" placeholder="Fixar mensagem ou regra da sala..." class="w-full bg-transparent border-0 border-b border-indigo-200 p-0 text-xs focus:ring-0 focus:border-indigo-500 placeholder-indigo-300">
                        <button type="submit" class="text-indigo-600 font-bold hover:text-indigo-900">Guardar</button>
                    </form>
                @else
                    <p class="font-medium">{{ $room->pinned_message }}</p>
                @endif
            </div>
        </div>
    @endif
</header>