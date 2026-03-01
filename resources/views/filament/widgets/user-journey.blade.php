<x-filament-widgets::widget>
    <x-filament::section>
        @php $stats = $this->getJourneyStats(); @endphp

        <div class="space-y-6">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Mapa de Jornada (30 dias)</h3>

            <div class="grid grid-cols-4 gap-4">
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['new_users'] }}</p>
                    <p class="text-[10px] font-bold text-indigo-500/70 uppercase tracking-wider mt-1">Novos</p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['active_users'] }}</p>
                    <p class="text-[10px] font-bold text-emerald-500/70 uppercase tracking-wider mt-1">Ativos (7d)</p>
                </div>
                <div class="bg-violet-50 dark:bg-violet-900/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-violet-600 dark:text-violet-400">{{ $stats['total_logs'] }}</p>
                    <p class="text-[10px] font-bold text-violet-500/70 uppercase tracking-wider mt-1">Registos</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['total_posts'] }}</p>
                    <p class="text-[10px] font-bold text-amber-500/70 uppercase tracking-wider mt-1">Posts</p>
                </div>
            </div>

            {{-- Distribuição de Mood --}}
            <div>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Distribuição de Humor</p>
                <div class="flex items-end gap-2 h-20">
                    @for($i = 1; $i <= 5; $i++)
                        @php
                            $count = $stats['mood_distribution'][$i] ?? 0;
                            $max = max(1, max($stats['mood_distribution'] ?: [1]));
                            $height = ($count / $max) * 100;
                            $colors = [1 => 'bg-slate-400', 2 => 'bg-blue-400', 3 => 'bg-indigo-400', 4 => 'bg-emerald-400', 5 => 'bg-amber-400'];
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-[9px] font-bold text-gray-400">{{ $count }}</span>
                            <div class="{{ $colors[$i] }} rounded-t-md w-full transition-all" style="height: {{ max(4, $height) }}%"></div>
                            <span class="text-[9px] text-gray-500">{{ $i }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Top Salas --}}
            @if(!empty($stats['top_rooms']))
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Salas Mais Visitadas</p>
                    <div class="space-y-1.5">
                        @foreach($stats['top_rooms'] as $room)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $room->name }}</span>
                                <span class="text-gray-500 font-bold">{{ $room->visitors }} visitantes</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
