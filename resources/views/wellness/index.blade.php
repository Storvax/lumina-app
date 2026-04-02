<x-lumina-layout title="Programas de Bem-Estar | Lumina">
    <div class="py-10 pt-28 md:pt-32">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                    <i class="ri-plant-line text-teal-500"></i> Programas de Bem-Estar
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Iniciativas de saúde organizadas pela tua empresa para te apoiar.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-teal-50 border border-teal-200 rounded-2xl px-5 py-3 text-teal-700 text-sm font-medium flex items-center gap-2">
                    <i class="ri-check-line text-lg"></i> {{ session('success') }}
                </div>
            @endif

            @if($programs->isEmpty())
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-3xl p-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <i class="ri-calendar-event-line text-4xl text-slate-300 mb-3 block"></i>
                    <p class="text-slate-400 font-medium">A tua empresa ainda não tem programas ativos.</p>
                    <p class="text-slate-400 text-sm mt-1">Fala com o teu departamento de RH para lançar o primeiro.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 gap-6">
                    @foreach($programs as $program)
                        @php $enrolled = in_array($program->id, $enrolledIds); @endphp
                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col gap-4">
                            <div>
                                <h2 class="font-black text-slate-800 dark:text-white text-lg leading-snug">{{ $program->title }}</h2>
                                @if($program->description)
                                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ $program->description }}</p>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-3 text-xs font-bold text-slate-500">
                                <span class="flex items-center gap-1"><i class="ri-calendar-line"></i> {{ $program->starts_at->format('d/m') }} – {{ $program->ends_at->format('d/m/Y') }}</span>
                                <span class="flex items-center gap-1"><i class="ri-time-line"></i> {{ $program->duration_days }} dias</span>
                                @if($program->target_diary_days > 0)
                                    <span class="flex items-center gap-1"><i class="ri-book-2-line"></i> {{ $program->target_diary_days }} entradas no diário</span>
                                @endif
                                @if($program->target_meditations > 0)
                                    <span class="flex items-center gap-1"><i class="ri-mental-health-line"></i> {{ $program->target_meditations }} meditações</span>
                                @endif
                            </div>

                            @if($enrolled)
                                <div class="mt-auto flex items-center gap-2 text-teal-600 bg-teal-50 dark:bg-teal-900/30 rounded-xl px-4 py-2.5 text-sm font-bold">
                                    <i class="ri-check-double-line text-lg"></i> Inscrito — continua o bom trabalho!
                                </div>
                            @else
                                <form action="{{ route('wellness.enroll', $program) }}" method="POST" class="mt-auto">
                                    @csrf
                                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                                        <i class="ri-add-line"></i> Inscrever-me
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-lumina-layout>
