<footer class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 pt-20 pb-10 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-12 mb-16">
            <div class="col-span-1 md:col-span-1 space-y-4">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 dark:bg-white flex items-center justify-center text-white dark:text-slate-900 font-bold text-lg">L</div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white">Lumina.</span>
                </a>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Democratizar o acesso ao bem-estar mental em Portugal, criando pontes entre pessoas e profissionais.
                </p>
                <div class="flex gap-4 pt-2">
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:text-indigo-600 transition-colors"><i class="ri-instagram-line"></i></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:text-indigo-600 transition-colors"><i class="ri-twitter-x-line"></i></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:text-indigo-600 transition-colors"><i class="ri-linkedin-fill"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6">Plataforma</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="{{ route('rooms.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">A Fogueira</a></li>
                    <li><a href="{{ route('forum.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Mural da Esperança</a></li>
                    <li><a href="{{ route('calm.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Zona Calma</a></li>
                    <li><a href="{{ route('library.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Biblioteca</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6">Legal</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Termos de Uso</a></li>
                    <li><a href="{{ route('privacy.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Privacidade e Dados</a></li>
                </ul>
            </div>

            <div>
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/50 p-5 rounded-2xl">
                    <p class="text-xs font-bold text-amber-700 dark:text-amber-500 uppercase mb-2 flex items-center gap-1">
                        <i class="ri-alert-line"></i> Importante
                    </p>
                    <p class="text-xs text-amber-800/80 dark:text-amber-200/80 leading-relaxed">
                        A Lumina não presta atos médicos. Em caso de emergência ou risco de vida, liga imediatamente para o <span class="font-bold">112</span> ou <span class="font-bold">SNS24 (808 24 24 24)</span>.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="border-t border-slate-100 dark:border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-slate-400">© {{ date('Y') }} Lumina Portugal. Todos os direitos reservados.</p>
            <p class="text-xs text-slate-400 flex items-center gap-1">Feito com <i class="ri-heart-fill text-rose-400"></i> e empatia.</p>
        </div>
    </div>
</footer>