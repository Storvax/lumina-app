<x-lumina-layout title="Mural da Esperança | Lumina">
    
    <x-slot name="css">
        .blur-content { filter: blur(8px); user-select: none; pointer-events: none; transition: 0.4s ease-out; }
        .revealed .blur-content { filter: none; user-select: auto; pointer-events: auto; }
        .revealed .overlay-warning { opacity: 0; pointer-events: none; }
        .overlay-warning { transition: opacity 0.3s; }
    </x-slot>

    <x-slot name="actionButton">
        <button onclick="togglePostModal()" class="hidden md:flex bg-slate-900 text-white hover:bg-slate-800 px-5 py-2.5 rounded-full text-sm font-bold items-center gap-2 transition-all shadow-lg shadow-slate-900/20 active:scale-95">
            <i class="ri-quill-pen-line"></i> Escrever
        </button>
    </x-slot>

    <section class="relative pt-20 pb-12 overflow-hidden text-center">
        <div class="max-w-4xl mx-auto px-6 animate-fade-up">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/60 border border-white text-indigo-600 text-xs font-bold uppercase tracking-wider shadow-sm backdrop-blur-sm mb-6">
                🌻 Comunidade
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
                O Mural da <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-violet-600">Esperança.</span>
            </h1>
            
            <p class="text-lg text-slate-500 leading-relaxed max-w-xl mx-auto mb-8">
                Partilha a tua história, deixa um desabafo ou acende uma luz. As tuas palavras podem ser o abrigo de alguém.
            </p>

            <div class="glass p-2 rounded-2xl inline-flex flex-wrap justify-center gap-2">
                <button onclick="filterPosts('all')" id="btn-all" class="filter-btn px-6 py-3 rounded-xl bg-white shadow-sm border border-slate-100 text-slate-800 font-bold text-sm hover:-translate-y-0.5 transition-all ring-2 ring-indigo-500/10">Tudo</button>
                <button onclick="filterPosts('hope')" id="btn-hope" class="filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 hover:text-emerald-600 transition-all">🌱 Esperança</button>
                <button onclick="filterPosts('vent')" id="btn-vent" class="filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 hover:text-rose-500 transition-all">❤️‍🩹 Desabafo</button>
                <button onclick="filterPosts('anxiety')" id="btn-anxiety" class="filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 hover:text-amber-500 transition-all">🌩️ Ansiedade</button>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-6 pb-24">
        <div id="posts-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 transition-opacity duration-300">
            @include('forum.partials.posts')
        </div>
    </main>

    <div id="postModal" class="fixed inset-0 z-[80] hidden">
        <div id="postModalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" onclick="togglePostModal()"></div>
        <div class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none">
            <div id="postModalPanel" class="bg-white md:rounded-[2rem] rounded-t-[2rem] shadow-2xl w-full max-w-lg md:mx-4 transform transition-all translate-y-full md:translate-y-10 opacity-0 pointer-events-auto flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-800">Partilhar no Mural</h3>
                    <button onclick="togglePostModal()" class="w-10 h-10 rounded-full bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-500 transition-colors"><i class="ri-close-line text-xl"></i></button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <form id="create-post-form" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Título</label>
                            <input name="title" type="text" maxlength="60" placeholder="Resumindo numa frase..." required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-bold text-slate-700 placeholder:font-normal">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">A tua história</label>
                            <textarea name="content" rows="5" maxlength="1000" placeholder="Escreve aqui. Este é um espaço seguro." required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all text-slate-600 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Como te sentes?</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="tag" class="peer sr-only" value="hope" required>
                                    <span class="flex flex-col items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-200 peer-checked:ring-1 peer-checked:ring-emerald-200 transition-all"><span class="text-xl mb-1">🌱</span><span class="text-xs font-bold">Esperança</span></span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="tag" class="peer sr-only" value="vent">
                                    <span class="flex flex-col items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-checked:border-rose-200 peer-checked:ring-1 peer-checked:ring-rose-200 transition-all"><span class="text-xl mb-1">❤️‍🩹</span><span class="text-xs font-bold">Desabafo</span></span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="tag" class="peer sr-only" value="anxiety">
                                    <span class="flex flex-col items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-checked:border-amber-200 peer-checked:ring-1 peer-checked:ring-amber-200 transition-all"><span class="text-xl mb-1">🌩️</span><span class="text-xs font-bold">Ansiedade</span></span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100 transition-colors cursor-pointer">
                            <input type="checkbox" name="is_sensitive" id="toggle-sensitive" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="toggle-sensitive" class="flex-1 cursor-pointer">
                                <span class="block text-sm font-bold text-slate-700">Conteúdo Sensível</span>
                                <span class="block text-xs text-slate-500">Iremos desfocar o texto na listagem.</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-slate-900/20 transition-all active:scale-95 flex items-center justify-center gap-2"><span>Publicar</span> <i class="ri-send-plane-fill"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            // Lógica do Modal de Post
            const postModal = document.getElementById('postModal');
            const postPanel = document.getElementById('postModalPanel');
            const postBackdrop = document.getElementById('postModalBackdrop');
            window.togglePostModal = function() {
                if (postModal.classList.contains('hidden')) {
                    postModal.classList.remove('hidden');
                    setTimeout(() => { postBackdrop.classList.remove('opacity-0'); postPanel.classList.remove('translate-y-full', 'md:translate-y-10', 'opacity-0'); }, 10);
                } else {
                    postBackdrop.classList.add('opacity-0');
                    postPanel.classList.add('translate-y-full', 'md:translate-y-10', 'opacity-0');
                    setTimeout(() => { postModal.classList.add('hidden'); }, 300);
                }
            }

            // AJAX Filter
            async function filterPosts(tag) {
                const grid = document.getElementById('posts-grid');
                grid.style.opacity = '0.5';
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.className = "filter-btn px-6 py-3 rounded-xl bg-transparent border border-transparent text-slate-500 font-medium text-sm hover:bg-white/50 transition-all";
                });
                const activeBtn = document.getElementById(`btn-${tag}`);
                if(activeBtn) activeBtn.className = "filter-btn px-6 py-3 rounded-xl bg-white shadow-sm border border-slate-100 text-slate-800 font-bold text-sm transition-all ring-2 ring-indigo-500/10";

                try {
                    const response = await axios.get(`{{ route('forum.index') }}?tag=${tag}`);
                    grid.innerHTML = response.data;
                    const newUrl = tag === 'all' ? '{{ route('forum.index') }}' : `{{ route('forum.index') }}?tag=${tag}`;
                    window.history.pushState(null, '', newUrl);
                } catch (error) { console.error(error); } finally { grid.style.opacity = '1'; }
            }

            // AJAX Create Post
            const createForm = document.getElementById('create-post-form');
            if(createForm) {
                createForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = createForm.querySelector('button[type="submit"]');
                    const original = btn.innerHTML;
                    btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>'; btn.disabled = true;
                    try { await axios.post('{{ route("forum.store") }}', new FormData(createForm)); window.location.reload(); }
                    catch (error) { alert("Erro ao publicar."); btn.innerHTML = original; btn.disabled = false; }
                });
            }

            // AJAX React
            window.react = async function(postId, type, btn) {
                btn.classList.add('scale-125'); setTimeout(() => btn.classList.remove('scale-125'), 200);
                const countSpan = btn.querySelector(`span[class*="count-"]`);
                let current = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = current + 1; 
                try { await axios.post(`/mural/${postId}/reagir`, { type: type }); } catch (e) { countSpan.textContent = current; }
            };
        </script>
    </x-slot>
</x-lumina-layout>