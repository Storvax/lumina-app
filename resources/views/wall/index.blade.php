<x-lumina-layout title="The Wall | Lumina">
    <div class="py-12 pt-32">
        <div class="max-w-7xl mx-auto px-6">

            <x-emotional-breadcrumb :items="[['label' => 'The Wall']]" />

            <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                        <i class="ri-gallery-line text-violet-500"></i> The Wall
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Expressão artística da comunidade. Partilha o que sentes através de imagens.</p>
                </div>

                <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold px-5 py-2.5 rounded-full transition-colors shadow-sm">
                    <i class="ri-upload-2-line"></i> Partilhar
                </button>
            </div>

            @if(session('status') === 'wall-post-submitted')
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800 rounded-2xl text-sm text-teal-700 dark:text-teal-300 flex items-center gap-2">
                    <i class="ri-checkbox-circle-line text-teal-500"></i>
                    A tua imagem foi submetida e será visível após revisão.
                </div>
            @endif

            {{-- Galeria masonry --}}
            @if($posts->count() > 0)
                <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
                    @foreach($posts as $post)
                        <div class="break-inside-avoid bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-lg transition-shadow">
                            @if($post->is_sensitive)
                                <div x-data="{ revealed: false }" class="relative">
                                    <img src="{{ Storage::url($post->image_path) }}"
                                         alt="{{ $post->caption ?? 'Expressão artística' }}"
                                         class="w-full transition-all duration-500"
                                         :class="revealed ? '' : 'blur-lg'"
                                         loading="lazy">
                                    <div x-show="!revealed" @click="revealed = true"
                                         class="absolute inset-0 flex items-center justify-center bg-white/60 dark:bg-slate-900/60 backdrop-blur-sm cursor-pointer">
                                        <span class="text-xs font-bold text-slate-500 bg-white dark:bg-slate-800 px-3 py-1.5 rounded-full shadow">
                                            <i class="ri-eye-off-line mr-1"></i> Conteúdo sensível — clica para ver
                                        </span>
                                    </div>
                                </div>
                            @else
                                <img src="{{ Storage::url($post->image_path) }}"
                                     alt="{{ $post->caption ?? 'Expressão artística' }}"
                                     class="w-full"
                                     loading="lazy">
                            @endif

                            @if($post->caption)
                                <div class="px-4 py-3">
                                    <p class="text-sm text-slate-600 dark:text-slate-300 italic">"{{ $post->caption }}"</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="flex flex-col items-center py-20 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <i class="ri-palette-line text-5xl text-slate-300 dark:text-slate-600 mb-4"></i>
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">O mural ainda está vazio.</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sê o primeiro a partilhar uma expressão artística.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de upload --}}
    <div id="upload-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full relative z-10 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="ri-image-add-line text-violet-500"></i> Partilhar no The Wall
            </h3>

            <form method="POST" action="{{ route('wall.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Imagem</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required
                           class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-violet-50 file:text-violet-600 hover:file:bg-violet-100 transition-colors">
                    <p class="text-[10px] text-slate-400 mt-1">JPG, PNG ou WebP. Máximo 5MB.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Legenda (opcional)</label>
                    <input type="text" name="caption" maxlength="100" placeholder="O que representa esta imagem?"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-sm px-4 py-2.5 focus:ring-violet-500">
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-3 text-xs text-amber-700 dark:text-amber-400">
                    <i class="ri-information-line mr-1"></i>
                    A imagem será revista antes de ficar visível na galeria.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')"
                            class="flex-1 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-bold transition-colors shadow-sm">
                        Submeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-lumina-layout>
