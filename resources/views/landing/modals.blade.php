<div id="sosModal" class="fixed inset-0 z-[100] hidden">
    <div id="modalOverlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity cursor-pointer"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4 animate-fade-up">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-rose-100">
            <div class="bg-rose-50 p-6 text-center border-b border-rose-100">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-500 text-3xl"><i class="ri-alarm-warning-fill"></i></div>
                <h3 class="text-2xl font-bold text-slate-800">Ajuda Imediata</h3>
                <p class="text-slate-600 mt-2 text-sm">Não estás sozinho. Estas linhas estão disponíveis agora.</p>
            </div>
            <div class="p-6 space-y-4">
                <a href="tel:112" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-rose-50 hover:border-rose-200 transition-colors group">
                    <div class="flex items-center gap-4"><span class="text-2xl font-black text-slate-800 group-hover:text-rose-600">112</span><div class="text-left"><p class="font-bold text-slate-800">Emergência Nacional</p><p class="text-xs text-slate-500">Risco de vida iminente</p></div></div>
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-rose-500 shadow-sm"><i class="ri-phone-fill"></i></div>
                </a>
                <a href="tel:808242424" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-50 hover:border-blue-200 transition-colors group">
                    <div class="flex items-center gap-4"><span class="text-xl font-bold text-slate-800 group-hover:text-blue-600">SNS 24</span><div class="text-left"><p class="font-bold text-slate-800">Apoio Psicológico</p><p class="text-xs text-slate-500">Disponível 24h por dia</p></div></div>
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-500 shadow-sm"><i class="ri-phone-fill"></i></div>
                </a>
            </div>
            <div class="bg-slate-50 p-4 text-center">
                <button id="modalClose" class="text-slate-500 font-semibold hover:text-slate-800 text-sm">Cancelar / Voltar ao site</button>
            </div>
        </div>
    </div>
</div>

<a href="https://www.google.pt" class="fixed bottom-6 right-6 z-[60] bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full shadow-xl flex items-center gap-2 transition-transform hover:scale-105 border-4 border-white ring-2 ring-red-100" title="Sair rapidamente para o Google">
    <i class="ri-eye-off-line text-xl"></i> <span class="hidden md:inline">Saída Rápida</span>
</a>

<div id="floatingPlayer" class="hidden fixed bottom-24 md:bottom-6 left-0 right-0 mx-auto w-[92%] md:w-fit z-40 bg-white/60 md:bg-white/95 backdrop-blur-md rounded-2xl md:rounded-full shadow-2xl border border-slate-200/50 px-4 py-3 flex items-center gap-4 animate-fade-up transition-all duration-300">
    <div class="flex items-center gap-3 overflow-hidden">
        <div class="w-10 h-10 shrink-0 rounded-full bg-primary-100/80 text-primary-600 flex items-center justify-center animate-pulse"><i class="ri-music-fill"></i></div>
        <div class="flex flex-col min-w-0">
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider truncate">A tocar</span>
            <span id="playerTitle" class="text-sm font-bold text-slate-900 truncate max-w-[150px] md:max-w-[200px]">Som de Portugal</span>
        </div>
    </div>
    <div class="flex items-center gap-2 ml-auto shrink-0">
        <button id="playerControlBtn" class="w-10 h-10 rounded-full bg-white/50 hover:bg-white/80 flex items-center justify-center text-slate-700 transition-colors backdrop-blur-sm"><i class="ri-pause-fill text-lg"></i></button>
        <button id="playerCloseBtn" class="w-10 h-10 rounded-full bg-rose-50/50 hover:bg-rose-100/80 flex items-center justify-center text-rose-500 transition-colors backdrop-blur-sm"><i class="ri-close-line text-lg"></i></button>
    </div>
</div>