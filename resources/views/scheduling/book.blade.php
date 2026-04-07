<x-lumina-layout title="Agendar Sessão | Lumina">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-teal-600 mb-4 transition-colors">
                    <i class="ri-arrow-left-line"></i> Voltar
                </a>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white">Agendar Sessão</h1>
                <p class="text-slate-500 text-sm mt-1">Com Dr(a). {{ $therapist->name ?? $therapist->user->name }}</p>
            </div>

            @if ($errors->any())
                <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (empty($slots))
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-10 text-center">
                    <div class="w-14 h-14 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="ri-calendar-close-line"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 dark:text-white mb-2">Sem disponibilidade nos próximos 14 dias</h3>
                    <p class="text-slate-500 text-sm">Tenta mais tarde ou contacta o terapeuta diretamente.</p>
                </div>
            @else
                <form method="POST" action="{{ route('sessions.store', $therapist) }}" class="space-y-6">
                    @csrf

                    {{-- Tipo de sessão --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
                        <label class="block text-sm font-bold text-slate-700 dark:text-white mb-3">Tipo de sessão</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="session_type" value="video" checked class="sr-only peer">
                                <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/20 transition-all text-center">
                                    <i class="ri-video-chat-line text-2xl text-teal-600 dark:text-teal-400"></i>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">Vídeo</p>
                                    <p class="text-xs text-slate-500">Online, pela plataforma</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="session_type" value="in_person" class="sr-only peer">
                                <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/20 transition-all text-center">
                                    <i class="ri-building-line text-2xl text-teal-600 dark:text-teal-400"></i>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">Presencial</p>
                                    <p class="text-xs text-slate-500">No consultório</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Escolha de horário --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
                        <label class="block text-sm font-bold text-slate-700 dark:text-white mb-3">Escolhe um horário</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto pr-1">
                            @foreach ($slots as $slot)
                                <label class="cursor-pointer">
                                    <input type="radio" name="scheduled_at" value="{{ $slot['value'] }}" class="sr-only peer" required>
                                    <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/20 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all text-sm font-medium text-slate-700 dark:text-slate-300 peer-checked:text-teal-700 dark:peer-checked:text-teal-300 min-h-[44px] flex items-center">
                                        <i class="ri-time-line mr-2 text-slate-400 peer-checked:text-teal-500"></i>
                                        {{ $slot['label'] }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notas do paciente --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
                        <label for="patient_notes" class="block text-sm font-bold text-slate-700 dark:text-white mb-1">
                            Notas para o terapeuta <span class="font-normal text-slate-400">(opcional)</span>
                        </label>
                        <p class="text-xs text-slate-500 mb-3">Partilha o que gostarias de abordar nesta sessão.</p>
                        <textarea
                            id="patient_notes"
                            name="patient_notes"
                            rows="4"
                            maxlength="1000"
                            placeholder="Ex: Quero falar sobre ansiedade no trabalho..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm p-3 focus:ring-2 focus:ring-teal-500 outline-none resize-none">{{ old('patient_notes') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-teal-600 text-white font-bold text-base rounded-2xl hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/20 min-h-[44px]">
                        Enviar Pedido de Sessão
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-lumina-layout>
