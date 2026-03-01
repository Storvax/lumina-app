<x-filament-widgets::widget>
    <x-filament::section>
        @php $data = $this->getData(); @endphp

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Temperatura da Comunidade</h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                    {{ $data['level'] === 'green' ? 'bg-emerald-50 text-emerald-700' : '' }}
                    {{ $data['level'] === 'yellow' ? 'bg-amber-50 text-amber-700' : '' }}
                    {{ $data['level'] === 'red' ? 'bg-rose-50 text-rose-700' : '' }}">
                    <span class="w-2 h-2 rounded-full
                        {{ $data['level'] === 'green' ? 'bg-emerald-500' : '' }}
                        {{ $data['level'] === 'yellow' ? 'bg-amber-500' : '' }}
                        {{ $data['level'] === 'red' ? 'bg-rose-500' : '' }}"></span>
                    {{ $data['level'] === 'green' ? 'Tranquilo' : ($data['level'] === 'yellow' ? 'Atenção' : 'Alerta') }}
                </span>
            </div>

            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div class="h-3 rounded-full transition-all duration-500
                    {{ $data['level'] === 'green' ? 'bg-emerald-500' : '' }}
                    {{ $data['level'] === 'yellow' ? 'bg-amber-500' : '' }}
                    {{ $data['level'] === 'red' ? 'bg-rose-500' : '' }}"
                     style="width: {{ $data['score'] }}%"></div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2.5">
                    <span class="text-gray-500 dark:text-gray-400">Mood médio</span>
                    <p class="font-bold text-gray-800 dark:text-gray-200">{{ $data['avg_mood'] }}/5</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2.5">
                    <span class="text-gray-500 dark:text-gray-400">Msgs/hora</span>
                    <p class="font-bold text-gray-800 dark:text-gray-200">{{ $data['messages_per_hour'] }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2.5">
                    <span class="text-gray-500 dark:text-gray-400">Alto risco</span>
                    <p class="font-bold {{ $data['high_risk_posts'] > 0 ? 'text-rose-600' : 'text-gray-800 dark:text-gray-200' }}">{{ $data['high_risk_posts'] }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2.5">
                    <span class="text-gray-500 dark:text-gray-400">Sensíveis</span>
                    <p class="font-bold text-gray-800 dark:text-gray-200">{{ $data['sensitive_messages'] }}</p>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
