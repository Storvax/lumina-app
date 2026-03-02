{{--
    Breadcrumb Emocional — navegação contextual com linguagem empática.

    Uso: <x-emotional-breadcrumb :items="[
        ['label' => 'Zona Calma', 'route' => 'calm.index'],
        ['label' => 'Respiração'],
    ]" />

    O último item é sempre o item activo (sem link).
--}}
@props(['items' => []])

@if(count($items) > 0)
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 font-medium">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors flex items-center gap-1">
                    <i class="ri-home-smile-2-line text-sm"></i>
                    <span class="hidden sm:inline">Início</span>
                </a>
            </li>
            @foreach($items as $index => $item)
                <li class="flex items-center gap-1.5">
                    <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600"></i>
                    @if($index < count($items) - 1 && isset($item['route']))
                        <a href="{{ route($item['route']) }}" class="hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-slate-600 dark:text-slate-300 font-bold">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
