<x-lumina-layout title="Meditações & Mindfulness | Lumina">
    <div class="py-10 pt-28 md:pt-32">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

            <div class="mb-8">
                <a href="{{ route('calm.index') }}" class="text-sm text-slate-400 hover:text-indigo-500 flex items-center gap-1 mb-3 transition-colors">
                    <i class="ri-arrow-left-s-line"></i> Zona Calma
                </a>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                    <i class="ri-mental-health-line text-teal-500"></i> Meditações Guiadas
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Encontra um momento de silêncio. Não precisas de experiência — só de presença.</p>
            </div>

            @if($categories->isEmpty())
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <i class="ri-moon-line text-4xl text-slate-300 mb-3 block"></i>
                    <p class="text-slate-400 font-medium">As meditações estão a ser preparadas. Volta em breve.</p>
                </div>
            @else
                @php
                    $categoryMeta = [
                        'breathing'     => ['icon' => 'ri-lungs-line',         'label' => 'Respiração',        'color' => 'teal'],
                        'body_scan'     => ['icon' => 'ri-body-scan-line',      'label' => 'Body Scan',         'color' => 'indigo'],
                        'visualization' => ['icon' => 'ri-eye-line',            'label' => 'Visualização',      'color' => 'violet'],
                        'sleep'         => ['icon' => 'ri-moon-clear-line',     'label' => 'Sono & Descanso',   'color' => 'blue'],
                        'anxiety'       => ['icon' => 'ri-hearts-line',         'label' => 'Ansiedade',         'color' => 'rose'],
                        'gratitude'     => ['icon' => 'ri-sparkling-2-line',    'label' => 'Gratidão',          'color' => 'amber'],
                    ];
                @endphp

                <div class="space-y-10">
                    @foreach($categories as $cat => $items)
                        @php
                            $meta   = $categoryMeta[$cat] ?? ['icon' => 'ri-play-circle-line', 'label' => ucfirst($cat), 'color' => 'slate'];
                            $color  = $meta['color'];
                            $bgMap  = ['teal' => 'bg-teal-50 dark:bg-teal-900/20 border-teal-100 dark:border-teal-800', 'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-100 dark:border-indigo-800', 'violet' => 'bg-violet-50 dark:bg-violet-900/20 border-violet-100 dark:border-violet-800', 'blue' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800', 'rose' => 'bg-rose-50 dark:bg-rose-900/20 border-rose-100 dark:border-rose-800', 'amber' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800', 'slate' => 'bg-slate-50 dark:bg-slate-800 border-slate-100 dark:border-slate-700'];
                            $textMap = ['teal' => 'text-teal-600 dark:text-teal-400', 'indigo' => 'text-indigo-600 dark:text-indigo-400', 'violet' => 'text-violet-600 dark:text-violet-400', 'blue' => 'text-blue-600 dark:text-blue-400', 'rose' => 'text-rose-600 dark:text-rose-400', 'amber' => 'text-amber-600 dark:text-amber-400', 'slate' => 'text-slate-600 dark:text-slate-400'];
                        @endphp

                        <section>
                            <h2 class="text-lg font-black text-slate-700 dark:text-white flex items-center gap-2 mb-4">
                                <i class="{{ $meta['icon'] }} {{ $textMap[$color] }} text-xl"></i> {{ $meta['label'] }}
                            </h2>
                            <div class="grid sm:grid-cols-2 gap-4">
                                @foreach($items as $meditation)
                                    <div x-data="{ playing: false, audio: null }"
                                         class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">

                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div>
                                                <h3 class="font-bold text-slate-800 dark:text-white text-sm leading-snug">{{ $meditation->title }}</h3>
                                                @if($meditation->description)
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $meditation->description }}</p>
                                                @endif
                                            </div>
                                            <span class="flex-shrink-0 text-xs font-bold text-slate-400 bg-slate-50 dark:bg-slate-700 px-2 py-1 rounded-lg">
                                                {{ $meditation->duration_formatted }}
                                            </span>
                                        </div>

                                        @if($meditation->audio_url)
                                            <div class="mt-3">
                                                <button
                                                    @click="
                                                        if (!audio) {
                                                            audio = new Audio('{{ $meditation->audio_url }}');
                                                            audio.onended = () => playing = false;
                                                        }
                                                        if (playing) { audio.pause(); audio.currentTime = 0; playing = false; }
                                                        else { audio.play(); playing = true; }
                                                    "
                                                    :class="playing ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-600' : 'bg-{{ $color }}-50 dark:bg-{{ $color }}-900/30 text-{{ $color }}-600'"
                                                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold transition-colors">
                                                    <i :class="playing ? 'ri-stop-circle-line' : 'ri-play-circle-line'" class="text-lg"></i>
                                                    <span x-text="playing ? 'Pausar' : 'Ouvir Meditação'"></span>
                                                </button>
                                            </div>
                                        @else
                                            <div class="mt-3 flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-700 text-slate-400 text-xs font-bold">
                                                <i class="ri-time-line"></i> Em breve
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif

            {{-- Nota sobre a prática --}}
            <div class="mt-10 p-5 bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800 rounded-2xl flex items-start gap-3">
                <i class="ri-leaf-line text-teal-500 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-teal-700 dark:text-teal-300">Pequenas práticas, grande diferença.</p>
                    <p class="text-xs text-teal-600/80 dark:text-teal-400/80 mt-0.5">
                        Mesmo 5 minutos por dia de respiração consciente reduzem o cortisol e melhoram o foco.
                        Não precisas de fazer tudo — escolhe o que ressoa contigo hoje.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-lumina-layout>
