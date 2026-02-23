<x-lumina-layout title="{{ __('Mural da Esperança | Lumina') }}">
    
    <x-slot name="css">
        /* Efeito de Vidro e Blur */
        .blur-content { filter: blur(8px); user-select: none; pointer-events: none; transition: 0.4s ease-out; }
        .revealed .blur-content { filter: none; user-select: auto; pointer-events: auto; }
        .revealed .overlay-warning { opacity: 0; pointer-events: none; }
        .overlay-warning { transition: opacity 0.3s; }

        /* Layout Masonry (Estilo Pinterest) */
        .masonry-grid { column-count: 1; column-gap: 1.5rem; }
        @media (min-width: 768px) { .masonry-grid { column-count: 2; } }
        @media (min-width: 1024px) { .masonry-grid { column-count: 3; } }
        .masonry-item { break-inside: avoid; margin-bottom: 1.5rem; }
    </x-slot>

    <x-slot name="actionButton">
        <button onclick="togglePostModal()" aria-label="{{ __('Escrever nova publicação') }}" class="hidden md:flex bg-slate-900 text-white hover:bg-slate-800 px-5 py-2.5 rounded-full text-sm font-bold items-center gap-2 transition-all shadow-lg shadow-slate-900/20 active:scale-95 focus-visible:ring-4 focus-visible:ring-indigo-500 focus-visible:outline-none">
            <i class="ri-quill-pen-line" aria-hidden="true"></i> {{ __('Escrever') }}
        </button>
    </x-slot>

    <section class="relative pt-20 pb-12 overflow-hidden text-center">
        <div class="max-w-4xl mx-auto px-6 animate-fade-up">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/60 border border-white text-indigo-600 text-xs font-bold uppercase tracking-wider shadow-sm backdrop-blur-sm mb-6" aria-hidden="true">
                🌻 {{ __('Comunidade') }}
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
                {{ __('O Mural da') }} <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-violet-600">{{ __('Esperança.') }}</span>
            </h1>
            
            <p class="text-lg text-slate-500 leading-relaxed max-w-xl mx-auto mb-8">
                {{ __('Partilha a tua história, deixa um desabafo ou acende uma luz. As tuas palavras podem ser o abrigo de alguém.') }}
            </p>

            <div class="max-w-md mx-auto mb-8 relative group z-30">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ri-search-line text-slate-400 group-focus-within:text-indigo-500 transition-colors" aria-hidden="true"></i>
                </div>
                <input type="text" 
                       id="search-input"
                       aria-label="{{ __('Procurar histórias ou palavras-chave') }}"
                       placeholder="{{ __('Procurar histórias, palavras-chave...') }}" 
                       class="w-full pl-11 pr-4 py-3.5 bg-white/80 backdrop-blur-sm border border-white/50 rounded-2xl shadow-sm text-slate-600 placeholder:text-slate-400 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/50 outline-none transition-all"
                       onkeyup="debounceSearch(this.value)">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <span id="search-loading" class="hidden text-indigo-500" aria-live="polite"><i class="ri-loader-4-line animate-spin"></i></span>
                </div>
            </div>

            <div class="glass p-2 rounded-2xl inline-flex flex-wrap justify-center gap-2" role="group" aria-label="{{ __('Filtros de Humor') }}">
                <button onclick="filterPosts('all', this)" id="btn-all" aria-pressed="true" class="filter-btn px-6 py-3 rounded-xl bg-white shadow-sm border border-slate-100 text-slate-800 font-bold text-sm hover:-translate-y-0.5 transition-all ring-2 ring-indigo-500/10 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500">{{ __('Tudo') }}</button>
                <button onclick="filterPosts('hope', this)" id="btn-hope" aria-pressed="false" class="filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 hover:text-emerald-600 transition-all focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500">🌱 {{ __('Esperança') }}</button>
                <button onclick="filterPosts('vent', this)" id="btn-vent" aria-pressed="false" class="filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 hover:text-rose-500 transition-all focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-rose-500">❤️‍🩹 {{ __('Desabafo') }}</button>
                <button onclick="filterPosts('anxiety', this)" id="btn-anxiety" aria-pressed="false" class="filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 hover:text-amber-500 transition-all focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-amber-500">🌩️ {{ __('Ansiedade') }}</button>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-6 pb-24" aria-live="polite">
        <div id="posts-grid" class="masonry-grid transition-opacity duration-300">
            @include('forum.partials.posts')
        </div>
        <div id="infinite-scroll-sentinel" class="w-full h-20 flex items-center justify-center mt-8 opacity-0 transition-opacity" aria-hidden="true">
            <div class="flex flex-col items-center gap-2 text-indigo-500">
                <i class="ri-loader-4-line text-2xl animate-spin"></i>
                <span class="text-xs font-bold uppercase tracking-widest">{{ __('A carregar mais histórias...') }}</span>
            </div>
        </div>
    </main>

    <div id="postModal" class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true" aria-labelledby="postModalTitle">
        <div id="postModalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" onclick="togglePostModal()" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none">
            <div id="postModalPanel" class="bg-white md:rounded-[2rem] rounded-t-[2rem] shadow-2xl w-full max-w-lg md:mx-4 transform transition-all translate-y-full md:translate-y-10 opacity-0 pointer-events-auto flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 id="postModalTitle" class="text-xl font-bold text-slate-800">{{ __('Partilhar no Mural') }}</h3>
                    <button onclick="togglePostModal()" aria-label="{{ __('Fechar modal') }}" class="w-10 h-10 rounded-full bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-500 transition-colors focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none"><i class="ri-close-line text-xl" aria-hidden="true"></i></button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <form id="create-post-form" class="space-y-6">
                        @csrf
                        <div>
                            <label for="postTitle" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Título') }}</label>
                            <input id="postTitle" name="title" type="text" maxlength="60" placeholder="{{ __('Resumindo numa frase...') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-bold text-slate-700 placeholder:font-normal">
                        </div>
                        <div>
                            <label for="postContent" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('A tua história') }}</label>
                            <textarea id="postContent" name="content" rows="5" maxlength="1000" placeholder="{{ __('Escreve aqui. Este é um espaço seguro.') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all text-slate-600 resize-none"></textarea>
                        </div>
                        <div>
                            <fieldset>
                                <legend class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">{{ __('Como te sentes?') }}</legend>
                                <div class="grid grid-cols-3 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="tag" class="peer sr-only" value="hope" required>
                                        <span class="flex flex-col items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-500 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-200 transition-all"><span class="text-xl mb-1" aria-hidden="true">🌱</span><span class="text-xs font-bold">{{ __('Esperança') }}</span></span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="tag" class="peer sr-only" value="vent">
                                        <span class="flex flex-col items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-500 peer-focus-visible:ring-2 peer-focus-visible:ring-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-checked:border-rose-200 transition-all"><span class="text-xl mb-1" aria-hidden="true">❤️‍🩹</span><span class="text-xs font-bold">{{ __('Desabafo') }}</span></span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="tag" class="peer sr-only" value="anxiety">
                                        <span class="flex flex-col items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-500 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-checked:border-amber-200 transition-all"><span class="text-xl mb-1" aria-hidden="true">🌩️</span><span class="text-xs font-bold">{{ __('Ansiedade') }}</span></span>
                                    </label>
                                </div>
                            </fieldset>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100 transition-colors cursor-pointer">
                            <input type="checkbox" name="is_sensitive" id="toggle-sensitive" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="toggle-sensitive" class="flex-1 cursor-pointer">
                                <span class="block text-sm font-bold text-slate-700">{{ __('Conteúdo Sensível') }}</span>
                                <span class="block text-xs text-slate-500">{{ __('Iremos desfocar o texto na listagem.') }}</span>
                            </label>
                        </div>
                        <div id="crisis-banner" class="hidden mb-6 bg-rose-50 border border-rose-100 rounded-xl p-4 animate-fade-in" role="alert">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-rose-100 text-rose-600 rounded-full shrink-0">
                                    <i class="ri-heart-pulse-fill text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-rose-700 text-sm mb-1">{{ __('Não estás sozinho(a).') }}</h4>
                                    <p class="text-xs text-rose-600/80 mb-3 leading-relaxed">
                                        {{ __('Parece que estás a passar por um momento difícil. Se precisares de falar com alguém agora mesmo:') }}
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="tel:112" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white focus-visible:ring-2 focus-visible:ring-rose-900 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                                            <i class="ri-phone-fill" aria-hidden="true"></i> {{ __('112 (Emergência)') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white focus-visible:ring-4 focus-visible:ring-indigo-500 font-bold py-4 rounded-xl shadow-lg shadow-slate-900/20 transition-all active:scale-95 flex items-center justify-center gap-2"><span>{{ __('Publicar') }}</span> <i class="ri-send-plane-fill" aria-hidden="true"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 z-[90] hidden" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div id="deleteBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" onclick="closeDeleteModal()" aria-hidden="true"></div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none p-4">
            <div id="deletePanel" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm pointer-events-auto transform transition-all scale-90 opacity-0 p-6 text-center">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl" aria-hidden="true"><i class="ri-delete-bin-line"></i></div>
                <h3 id="deleteModalTitle" class="text-xl font-bold text-slate-800 mb-2">{{ __('Apagar este post?') }}</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">{{ __('Esta ação é permanente e não pode ser desfeita.') }}</p>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="closeDeleteModal()" class="py-3 rounded-xl font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 transition-colors focus-visible:ring-2 focus-visible:ring-slate-500 outline-none">{{ __('Cancelar') }}</button>
                    <button id="confirm-delete-btn" class="py-3 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-lg shadow-rose-500/30 transition-all active:scale-95 focus-visible:ring-2 focus-visible:ring-rose-800 outline-none">{{ __('Apagar') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div id="reportModal" class="fixed inset-0 z-[90] hidden" role="dialog" aria-modal="true" aria-labelledby="reportModalTitle">
        <div id="reportBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" onclick="closeReportModal()" aria-hidden="true"></div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none p-4">
            <div id="reportPanel" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm pointer-events-auto transform transition-all scale-90 opacity-0 p-6">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl" aria-hidden="true"><i class="ri-flag-fill"></i></div>
                    <h3 id="reportModalTitle" class="text-lg font-bold text-slate-800">{{ __('Denunciar Conteúdo') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Ajuda-nos a manter a comunidade segura.') }}</p>
                </div>
                <div class="space-y-2 mb-6">
                    <button onclick="submitReport('spam')" class="w-full p-3 rounded-xl border border-slate-100 text-left text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-3 focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none"><span aria-hidden="true" class="text-lg">🤖</span> {{ __('Spam ou Publicidade') }}</button>
                    <button onclick="submitReport('hate')" class="w-full p-3 rounded-xl border border-slate-100 text-left text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-3 focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none"><span aria-hidden="true" class="text-lg">🤬</span> {{ __('Discurso de Ódio / Ofensivo') }}</button>
                    <button onclick="submitReport('risk')" class="w-full p-3 rounded-xl border border-rose-100 bg-rose-50/50 text-left text-sm font-bold text-rose-700 hover:bg-rose-100 transition-all flex items-center gap-3 focus-visible:ring-2 focus-visible:ring-rose-500 outline-none"><span aria-hidden="true" class="text-lg">🆘</span> {{ __('Risco de Suicídio') }}</button>
                </div>
                <button onclick="closeReportModal()" class="w-full py-3 text-sm font-bold text-slate-400 hover:text-slate-600 focus-visible:ring-2 focus-visible:ring-slate-400 outline-none rounded-xl">{{ __('Cancelar') }}</button>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            /**
             * Lógica de Focus Trap para Acessibilidade (WCAG)
             */
            let activeFocusTrap = null;
            let previouslyFocusedElement = null;

            function trapFocus(element) {
                if (activeFocusTrap) document.removeEventListener('keydown', activeFocusTrap);
                previouslyFocusedElement = document.activeElement;
                
                const focusable = element.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (!focusable.length) return;
                
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                
                activeFocusTrap = function(e) {
                    if (e.key === 'Escape') {
                        if(element.id === 'postModalPanel') togglePostModal();
                        if(element.id === 'deletePanel') closeDeleteModal();
                        if(element.id === 'reportPanel') closeReportModal();
                        return;
                    }
                    if (e.key !== 'Tab') return;
                    
                    if (e.shiftKey) {
                        if (document.activeElement === first) { last.focus(); e.preventDefault(); }
                    } else {
                        if (document.activeElement === last) { first.focus(); e.preventDefault(); }
                    }
                };
                
                document.addEventListener('keydown', activeFocusTrap);
                setTimeout(() => first.focus(), 100);
            }

            function releaseFocus() {
                if (activeFocusTrap) {
                    document.removeEventListener('keydown', activeFocusTrap);
                    activeFocusTrap = null;
                    if(previouslyFocusedElement) previouslyFocusedElement.focus();
                }
            }

            // --- Lógica do Modal de Criar Post ---
            const postModal = document.getElementById('postModal');
            const postPanel = document.getElementById('postModalPanel');
            const postBackdrop = document.getElementById('postModalBackdrop');
            
            window.togglePostModal = function() {
                if (postModal.classList.contains('hidden')) {
                    postModal.classList.remove('hidden');
                    setTimeout(() => { 
                        postBackdrop.classList.remove('opacity-0'); 
                        postPanel.classList.remove('translate-y-full', 'md:translate-y-10', 'opacity-0'); 
                        trapFocus(postPanel);
                    }, 10);
                } else {
                    postBackdrop.classList.add('opacity-0');
                    postPanel.classList.add('translate-y-full', 'md:translate-y-10', 'opacity-0');
                    releaseFocus();
                    setTimeout(() => { postModal.classList.add('hidden'); }, 300);
                }
            }

            // --- Lógica do Modal de Eliminar ---
            const deleteModal = document.getElementById('deleteModal');
            const deletePanel = document.getElementById('deletePanel');
            const deleteBackdrop = document.getElementById('deleteBackdrop');
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            let postToDeleteId = null;

            window.openDeleteModal = function(postId) {
                event.preventDefault(); event.stopPropagation();
                postToDeleteId = postId;
                deleteModal.classList.remove('hidden');
                setTimeout(() => {
                    deleteBackdrop.classList.remove('opacity-0');
                    deletePanel.classList.remove('scale-90', 'opacity-0');
                    deletePanel.classList.add('scale-100', 'opacity-100');
                    trapFocus(deletePanel);
                }, 10);
            }

            window.closeDeleteModal = function() {
                deleteBackdrop.classList.add('opacity-0');
                deletePanel.classList.remove('scale-100', 'opacity-100');
                deletePanel.classList.add('scale-90', 'opacity-0');
                releaseFocus();
                setTimeout(() => { deleteModal.classList.add('hidden'); postToDeleteId = null; }, 300);
            }

            confirmDeleteBtn.addEventListener('click', async () => {
                if(!postToDeleteId) return;
                const id = postToDeleteId;
                const card = document.getElementById(`post-card-${id}`);
                const btnContent = confirmDeleteBtn.innerHTML;
                confirmDeleteBtn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';
                
                try {
                    closeDeleteModal();
                    if(card) { card.style.transition = "all 0.5s ease"; card.style.transform = "scale(0.9)"; card.style.opacity = "0"; }
                    await axios.delete(`/mural/${id}`);
                    setTimeout(() => { if(card) card.remove(); }, 500);
                } catch (error) {
                    Swal.fire({ title: 'Erro!', text: 'Erro ao apagar post.', icon: 'error', customClass: { popup: 'rounded-3xl' } });
                    if(card) { card.style.opacity = "1"; card.style.transform = "scale(1)"; }
                } finally { confirmDeleteBtn.innerHTML = btnContent; }
            });

            // Filtros com atualização de aria-pressed para Screen Readers
            window.filterPosts = async function(tag, clickedBtn = null) {
                const grid = document.getElementById('posts-grid');
                grid.style.opacity = '0.5';
                
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.className = "filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 transition-all focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500";
                    btn.setAttribute('aria-pressed', 'false');
                });
                
                const activeBtn = clickedBtn || document.getElementById(`btn-${tag}`);
                if(activeBtn) {
                    activeBtn.className = "filter-btn px-6 py-3 rounded-xl bg-white shadow-sm border border-slate-100 text-slate-800 font-bold text-sm transition-all ring-2 ring-indigo-500/10 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500";
                    activeBtn.setAttribute('aria-pressed', 'true');
                }

                try {
                    const response = await axios.get(`{{ route('forum.index') }}?tag=${tag}`);
                    grid.innerHTML = response.data;
                    const newUrl = tag === 'all' ? '{{ route('forum.index') }}' : `{{ route('forum.index') }}?tag=${tag}`;
                    window.history.pushState(null, '', newUrl);
                } catch (error) { console.error(error); } finally { grid.style.opacity = '1'; }
            }

            window.react = async function(postId, type, btn) {
                btn.classList.add('scale-125'); setTimeout(() => btn.classList.remove('scale-125'), 200);
                const countSpan = btn.querySelector(`span[class*="count-"]`);
                let current = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = current + 1; 
                try { await axios.post(`/mural/${postId}/reagir`, { type: type }); } catch (e) { countSpan.textContent = current; }
            };

            let timeout = null;
            window.debounceSearch = function(query) {
                clearTimeout(timeout);
                const loader = document.getElementById('search-loading');
                if(loader) loader.classList.remove('hidden');
                timeout = setTimeout(() => { performSearch(query); }, 500);
            };

            async function performSearch(query) {
                const grid = document.getElementById('posts-grid');
                const loader = document.getElementById('search-loading');
                grid.style.opacity = '0.5';
                try {
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentTag = urlParams.get('tag') || 'all';
                    const response = await axios.get(`{{ route('forum.index') }}?tag=${currentTag}&search=${query}`);
                    grid.innerHTML = response.data;
                    const newUrl = `{{ route('forum.index') }}?tag=${currentTag}&search=${query}`;
                    window.history.pushState(null, '', newUrl);
                } catch (error) {} finally {
                    grid.style.opacity = '1';
                    if(loader) loader.classList.add('hidden');
                }
            }

            // Resto da Lógica Mantida (Moderação, Formulário de Criação...)
            const createForm = document.getElementById('create-post-form');
            if(createForm) {
                createForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = createForm.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>'; btn.disabled = true;
                    try { await axios.post('{{ route("forum.store") }}', new FormData(createForm)); window.location.reload(); } 
                    catch (error) { Swal.fire({ title: 'Erro!', text: 'Erro ao publicar.', icon: 'error' }); btn.innerHTML = originalText; btn.disabled = false; }
                });
            }

            let reportPostId = null;
            const reportModal = document.getElementById('reportModal');
            const reportPanel = document.getElementById('reportPanel');
            const reportBackdrop = document.getElementById('reportBackdrop');

            window.openReportModal = function(postId) {
                reportPostId = postId;
                reportModal.classList.remove('hidden');
                setTimeout(() => {
                    reportBackdrop.classList.remove('opacity-0');
                    reportPanel.classList.remove('scale-90', 'opacity-0');
                    reportPanel.classList.add('scale-100', 'opacity-100');
                    trapFocus(reportPanel);
                }, 10);
            };

            window.closeReportModal = function() {
                reportBackdrop.classList.add('opacity-0');
                reportPanel.classList.remove('scale-100', 'opacity-100');
                reportPanel.classList.add('scale-90', 'opacity-0');
                releaseFocus();
                setTimeout(() => { reportModal.classList.add('hidden'); }, 300);
            };

            window.submitReport = async function(reason) {
                if(!reportPostId) return;
                try {
                    await axios.post(`/mural/${reportPostId}/report`, { reason: reason });
                    closeReportModal();
                    Swal.fire({ title: 'Enviado', text: 'A equipa vai rever o conteúdo.', icon: 'success' });
                } catch (error) {}
            };

            window.toggleSave = async function(postId, btn) {
                const icon = btn.querySelector('i');
                icon.classList.add('scale-125'); setTimeout(() => icon.classList.remove('scale-125'), 200);
                try {
                    const response = await axios.post(`/mural/${postId}/save`);
                    if (response.data.saved) {
                        icon.classList.replace('ri-bookmark-line', 'ri-bookmark-fill');
                        icon.classList.add('text-indigo-600');
                    } else {
                        icon.classList.replace('ri-bookmark-fill', 'ri-bookmark-line');
                        icon.classList.remove('text-indigo-600');
                    }
                } catch (error) {}
            };

            let nextPage = 2;
            let lastPage = {{ $posts->lastPage() ?? 1 }};
            let isLoading = false;
            const sentinel = document.getElementById('infinite-scroll-sentinel');
            const observer = new IntersectionObserver(async (entries) => {
                if (entries[0].isIntersecting && !isLoading && nextPage <= lastPage) {
                    isLoading = true; sentinel.classList.remove('opacity-0');
                    try {
                        const params = new URLSearchParams(window.location.search);
                        params.set('page', nextPage);
                        const response = await axios.get(`{{ route('forum.index') }}?${params.toString()}`);
                        document.getElementById('posts-grid').insertAdjacentHTML('beforeend', response.data);
                        nextPage++;
                        if (nextPage > lastPage) { sentinel.innerHTML = '<span class="text-slate-400 text-xs">Chegaste ao fim. 🌱</span>'; setTimeout(() => sentinel.classList.add('opacity-0'), 2000); }
                    } catch (error) {} finally { isLoading = false; if(nextPage <= lastPage) sentinel.classList.add('opacity-0'); }
                }
            }, { rootMargin: '200px' });
            if (sentinel) observer.observe(sentinel);
        </script>
    </x-slot>
</x-lumina-layout>