<x-lumina-layout title="Editar Nota Clínica | Lumina PRO">
    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">

            <div class="mb-8">
                <a href="{{ route('clinical-notes.index', $patient) }}" class="text-sm text-slate-400 hover:text-indigo-500 flex items-center gap-1 mb-3 transition-colors">
                    <i class="ri-arrow-left-s-line"></i> Voltar às Notas
                </a>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white">Editar Nota Clínica</h1>
                <p class="text-slate-400 text-sm mt-0.5">Paciente: <strong>{{ $patient->pseudonym }}</strong></p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm">
                <form action="{{ route('clinical-notes.update', [$patient, $clinicalNote]) }}" method="POST" class="space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label for="session_date" class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Data da Sessão</label>
                        <input type="date" name="session_date" id="session_date"
                               value="{{ old('session_date', $clinicalNote->session_date?->format('Y-m-d')) }}"
                               max="{{ now()->toDateString() }}"
                               class="w-full sm:w-48 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                        @error('session_date')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="content" class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Nota Clínica</label>
                        <textarea name="content" id="content" rows="10"
                                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-400 focus:border-transparent resize-none">{{ old('content', $clinicalNote->content) }}</textarea>
                        @error('content')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-2">
                        <p class="text-xs text-slate-400 flex items-center gap-1"><i class="ri-lock-2-line"></i> Encriptado e visível apenas por ti</p>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            <i class="ri-save-line"></i> Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-lumina-layout>
