<section class="py-8 bg-slate-50 border-b border-slate-200/60 relative overflow-hidden">
    <div class="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="flex flex-col md:flex-row items-center justify-center gap-6 md:gap-16 divide-y md:divide-y-0 md:divide-x divide-slate-200">
            
            <div class="flex items-center gap-4 px-6 py-2 group">
                <div class="relative">
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-ping opacity-75"></span>
                    <div class="w-12 h-12 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-center text-green-500 text-xl group-hover:scale-110 transition-transform duration-300">
                        <i class="ri-user-smile-line"></i>
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-800 leading-none">{{ $communityStats['online'] + 24 }}</p> {{-- Fake +24 para demo, remove em prod --}}
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Pessoas nas Salas</p>
                </div>
            </div>

            <div class="flex items-center gap-4 px-6 py-2 group pt-4 md:pt-0">
                <div class="w-12 h-12 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-center text-indigo-500 text-xl group-hover:rotate-12 transition-transform duration-300">
                    <i class="ri-fire-line"></i>
                </div>
                <div>
                    <p class="text-lg font-black text-slate-800 leading-none truncate max-w-[150px]">{{ $communityStats['top_room'] }}</p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Sala + Ativa Hoje</p>
                </div>
            </div>

            <div class="flex items-center gap-4 px-6 py-2 group pt-4 md:pt-0">
                <div class="w-12 h-12 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-center text-rose-500 text-xl group-hover:-translate-y-1 transition-transform duration-300">
                    <i class="ri-heart-pulse-fill"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-800 leading-none">{{ $communityStats['posts_today'] }}</p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Histórias Hoje</p>
                </div>
            </div>

        </div>
    </div>
</section>