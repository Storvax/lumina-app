<x-lumina-layout title="Notas Clínicas | Lumina PRO">
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

            <div class="mb-8">
                <a href="{{ route('therapist.dashboard') }}" class="text-sm text-slate-400 hover:text-indigo-500 flex items-center gap-1 mb-3 transition-colors">
                    <i class="ri-arrow-left-s-line"></i> Voltar ao Dashboard
                </a>
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="ri-file-lock-line text-indigo-500"></i> Notas Clínicas
                        </h1>
                        <p class="text-slate-500 text-sm mt-0.5">Paciente: <strong>{{ $patient->pseudonym }}</strong> — encriptadas at-rest</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-teal-50 border border-teal-200 rounded-2xl px-5 py-3 text-teal-700 text-sm font-medium flex items-center gap-2">
                    <i class="ri-check-line text-lg"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Formulário de nova nota --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm mb-8">
                <h2 class="font-bold text-slate-700 dark:text-white mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <i class="ri-add-line text-indigo-500"></i> Nova Nota de Sessão
                </h2>
                <form action="{{ route('clinical-notes.store', $patient) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="session_date" class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Data da Sessão</label>
                        <input type="date" name="session_date" id="session_date"
                               value="{{ old('session_date', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}"
                               class="w-full sm:w-48 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                        @error('session_date')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="content" class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Nota Clínica</label>
                        <textarea name="content" id="content" rows="6"
                                  placeholder="Observações, intervenções realizadas, próximos passos..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-400 focus:border-transparent resize-none">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-slate-400 flex items-center gap-1"><i class="ri-lock-2-line"></i> Encriptado e visível apenas por ti</p>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            <i class="ri-save-line"></i> Guardar Nota
                        </button>
                    </div>
                </form>
            </div>

            {{-- Listagem de notas --}}
            <div class="space-y-4">
                @forelse($notes as $note)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex items-center gap-2">
                                <i class="ri-calendar-line text-slate-400 text-sm"></i>
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">
                                    {{ $note->session_date ? $note->session_date->format('d/m/Y') : $note->created_at->format('d/m/Y') }}
                                </span>
                                <span class="text-xs text-slate-400">• {{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('clinical-notes.edit', [$patient, $note]) }}"
                                   class="text-xs text-slate-500 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                                    <i class="ri-edit-line"></i> Editar
                                </a>
                                <form action="{{ route('clinical-notes.destroy', [$patient, $note]) }}" method="POST"
                                      onsubmit="return confirm('Tens a certeza que queres remover esta nota? A ação pode ser revertida por um administrador.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-600 flex items-center gap-1 transition-colors">
                                        <i class="ri-delete-bin-line"></i> Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $note->content }}</p>
                    </div>
                @empty
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-10 text-center border-2 border-dashed border-slate-200 dark:border-slate-700">
                        <i class="ri-file-text-line text-3xl text-slate-300 mb-2 block"></i>
                        <p class="text-slate-400 text-sm">Ainda não existem notas para este paciente.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-lumina-layout>
