<x-lumina-layout title="Gestão de Programas | Lumina Corporate">
    <div class="py-10 pt-28 md:pt-32">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">

            <div class="flex items-end justify-between gap-4 mb-8 flex-wrap">
                <div>
                    <a href="{{ route('corporate.dashboard') }}" class="text-sm text-slate-400 hover:text-indigo-500 flex items-center gap-1 mb-3 transition-colors">
                        <i class="ri-arrow-left-s-line"></i> Portal da Empresa
                    </a>
                    <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="ri-plant-line text-teal-500"></i> Programas de Bem-Estar
                    </h1>
                    <p class="text-slate-400 text-sm mt-0.5">{{ $company->name }}</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-teal-50 border border-teal-200 rounded-2xl px-5 py-3 text-teal-700 text-sm font-medium flex items-center gap-2">
                    <i class="ri-check-line text-lg"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Criar novo programa --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm mb-8">
                <h2 class="font-bold text-slate-700 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="ri-add-circle-line text-indigo-500"></i> Novo Programa
                </h2>
                <form action="{{ route('wellness.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Título</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   placeholder="Ex: 30 Dias de Mindfulness"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                            @error('title')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Início</label>
                                <input type="date" name="starts_at" value="{{ old('starts_at') }}" required
                                       min="{{ now()->toDateString() }}"
                                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Fim</label>
                                <input type="date" name="ends_at" value="{{ old('ends_at') }}" required
                                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Descrição (opcional)</label>
                        <textarea name="description" rows="2"
                                  placeholder="Descreve o objetivo do programa..."
                                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent resize-none">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Meta: Entradas no Diário</label>
                            <input type="number" name="target_diary_days" value="{{ old('target_diary_days', 0) }}" min="0" max="365"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Meta: Meditações</label>
                            <input type="number" name="target_meditations" value="{{ old('target_meditations', 0) }}" min="0" max="365"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            <i class="ri-save-line"></i> Criar Programa
                        </button>
                    </div>
                </form>
            </div>

            {{-- Listagem de programas --}}
            <h2 class="font-bold text-slate-700 dark:text-white text-sm uppercase tracking-wider mb-4">Programas da Empresa</h2>
            @forelse($programs as $program)
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm mb-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-slate-800 dark:text-white text-sm">{{ $program->title }}</h3>
                            <span @class([
                                'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase',
                                'bg-teal-100 text-teal-700' => $program->status === 'active',
                                'bg-slate-100 text-slate-500' => $program->status === 'draft',
                                'bg-indigo-100 text-indigo-700' => $program->status === 'completed',
                                'bg-amber-100 text-amber-700' => $program->status === 'archived',
                            ])>{{ $program->status }}</span>
                        </div>
                        <p class="text-xs text-slate-500">
                            {{ $program->starts_at->format('d/m/Y') }} – {{ $program->ends_at->format('d/m/Y') }}
                            · {{ $program->participants()->count() }} inscritos
                            · {{ $program->completion_rate }}% concluíram
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                        @if($program->target_diary_days > 0)
                            <span class="flex items-center gap-1 bg-slate-50 dark:bg-slate-700 px-2.5 py-1 rounded-lg">
                                <i class="ri-book-2-line"></i> {{ $program->target_diary_days }}d
                            </span>
                        @endif
                        @if($program->target_meditations > 0)
                            <span class="flex items-center gap-1 bg-slate-50 dark:bg-slate-700 px-2.5 py-1 rounded-lg">
                                <i class="ri-mental-health-line"></i> {{ $program->target_meditations }}m
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <p class="text-slate-400 text-sm">Ainda não existem programas criados.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-lumina-layout>
