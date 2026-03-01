{{--
    Tooltip contextual não-intrusivo para primeira interacção com features.

    Usa localStorage para rastrear features já vistas, evitando a necessidade
    de migrations. Limitado a 1 tooltip por página (via Alpine x-data).

    Uso: <x-contextual-tip feature="diary" title="O Teu Diário" description="Aqui podes escrever..." />
--}}
@props([
    'feature',
    'title',
    'description',
    'icon' => 'ri-lightbulb-line',
    'position' => 'bottom',
])

<div x-data="{
    visible: false,
    feature: '{{ $feature }}',
    storageKey: 'lumina_tip_{{ $feature }}',

    init() {
        if (!localStorage.getItem(this.storageKey)) {
            // Delay para não competir com o carregamento da página
            setTimeout(() => { this.visible = true; }, 800);
        }
    },

    dismiss() {
        localStorage.setItem(this.storageKey, '1');
        this.visible = false;
    }
}"
     x-show="visible"
     x-transition:enter="transition ease-out duration-400"
     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     x-cloak
     class="relative z-30 bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-indigo-500/10 border border-indigo-100 dark:border-indigo-800 p-4 max-w-xs"
     role="tooltip"
>
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400 flex items-center justify-center shrink-0">
            <i class="{{ $icon }} text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $title }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $description }}</p>
        </div>
        <button @click="dismiss()"
                class="w-6 h-6 rounded-full text-slate-300 hover:text-slate-500 dark:text-slate-600 dark:hover:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors shrink-0 -mt-1 -mr-1"
                aria-label="Fechar dica">
            <i class="ri-close-line text-sm"></i>
        </button>
    </div>
</div>
