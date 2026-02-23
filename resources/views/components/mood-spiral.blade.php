@props(['data'])

@php
    $centerX = 200;
    $centerY = 200;
    $a = 10; // Distância inicial ao centro
    $b = 4;  // Distância entre as voltas da espiral
@endphp

<div x-data="{ tooltip: '', tooltipX: 0, tooltipY: 0, show: false }" class="relative flex justify-center items-center w-full max-w-md mx-auto p-6 bg-slate-50 rounded-3xl">
    
    <svg viewBox="0 0 400 400" class="w-full h-auto drop-shadow-sm">
        <path d="
            @php
                $path = "M $centerX $centerY";
                foreach($data as $i => $log) {
                    $angle = 0.5 * $i; // Controla a abertura angular
                    $radius = $a + ($b * $angle);
                    $x = $centerX + ($radius * cos($angle));
                    $y = $centerY + ($radius * sin($angle));
                    $path .= " L $x $y";
                }
                echo $path;
            @endphp
        " fill="transparent" stroke="#e2e8f0" stroke-width="2" stroke-dasharray="4 4" />

        @foreach($data as $i => $log)
            @php
                $angle = 0.5 * $i;
                $radius = $a + ($b * $angle);
                $x = $centerX + ($radius * cos($angle));
                $y = $centerY + ($radius * sin($angle));
            @endphp
            
            <circle 
                cx="{{ $x }}" 
                cy="{{ $y }}" 
                r="6" 
                fill="{{ $log['color'] }}" 
                class="transition-all duration-300 hover:r-8 cursor-pointer origin-center hover:drop-shadow-md"
                @mouseenter="show = true; tooltip = '{{ $log['date'] }}: {{ addslashes($log['note']) }}'; tooltipX = $event.clientX; tooltipY = $event.clientY"
                @mouseleave="show = false"
                style="animation: fade-in 0.5s ease-out {{ $i * 50 }}ms both;"
            />
        @endforeach
    </svg>

    <div x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         :style="`top: ${tooltipY - 60}px; left: ${tooltipX}px; transform: translateX(-50%);`"
         class="fixed pointer-events-none z-50 bg-slate-800 text-white text-xs px-3 py-2 rounded-xl shadow-lg whitespace-nowrap">
        <span x-text="tooltip"></span>
    </div>
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: scale(0); }
    to { opacity: 1; transform: scale(1); }
}
</style>