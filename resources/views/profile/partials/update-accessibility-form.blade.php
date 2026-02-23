<section class="glass-card rounded-[2rem] p-6 md:p-8 mt-6">
    <header>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="ri-universal-access-line text-indigo-500"></i> Acessibilidade & Inclusão
        </h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            A Lumina adapta-se a ti. Ajusta a visualização para tornar o teu refúgio mais confortável.
        </p>
    </header>

    <form method="post" action="{{ route('profile.accessibility') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4 border-b border-slate-100 dark:border-slate-700 pb-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tamanho do Texto</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['sm' => 'Pequeno', 'base' => 'Normal', 'lg' => 'Grande', 'xl' => 'Enorme'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="a11y_text_size" value="{{ $val }}" class="peer sr-only" {{ auth()->user()->a11y_text_size === $val ? 'checked' : '' }}>
                            <div class="text-center px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl peer-checked:bg-indigo-600 peer-checked:text-white transition-colors text-sm font-medium">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-300">Modo de Leitura Cognitiva</span>
                    <span class="text-xs text-slate-500">Usa a fonte OpenDyslexic e aumenta o espaçamento.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="a11y_dyslexic_font" value="1" class="sr-only peer" {{ auth()->user()->a11y_dyslexic_font ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-300">Reduzir Animações</span>
                    <span class="text-xs text-slate-500">Desativa transições, vibrações e animações dinâmicas.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="a11y_reduced_motion" value="1" class="sr-only peer" {{ auth()->user()->a11y_reduced_motion ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all">
                Guardar Preferências
            </button>
            @if (session('status') === 'accessibility-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-600 font-medium">Gravado.</p>
            @endif
        </div>
    </form>
</section>