<x-lumina-layout title="Transparência e Privacidade | Lumina">

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none bg-[#F8FAFC] dark:bg-slate-900 transition-colors duration-500">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-200/30 dark:bg-indigo-900/20 rounded-full blur-[100px] mix-blend-multiply dark:mix-blend-lighten"></div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-12 pt-32">
        
        <header class="text-center mb-16">
            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-sm">
                <i class="ri-shield-check-fill"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white mb-4 tracking-tight">O Nosso Pacto Contigo</h1>
            <p class="text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                Na Lumina, acreditamos que a confiança é a base da cura. Escrevemos esta página em linguagem simples para que saibas exatamente como protegemos o teu espaço.
            </p>
        </header>

        <div class="space-y-8">
            
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
                <div class="flex items-start gap-6">
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-book-3-line"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-3">O teu Diário é estritamente privado</h2>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                            Tudo o que escreves no teu Diário Emocional e no teu Plano de Segurança é <strong>apenas teu</strong>. A nossa equipa de moderação não tem acesso ao conteúdo do teu diário, não o lê, nem o utiliza para outros fins. O único momento em que a nossa tecnologia analisa as tuas palavras é para te oferecer sugestões terapêuticas automáticas (Técnicas CBT), e essa análise ocorre num circuito fechado.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
                <div class="flex items-start gap-6">
                    <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-team-line"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-3">Mural e Fogueira (Visibilidade)</h2>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                            O conteúdo que publicas no Mural da Esperança e nas salas de chat da Fogueira é público para os membros da comunidade. Para garantir um ambiente seguro, estes espaços são monitorizados pela nossa equipa de moderadores e ouvintes treinados. Tens sempre a opção de publicar de forma <strong>anónima</strong> no Mural.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
                <div class="flex items-start gap-6">
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/30 text-amber-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-database-2-line"></i>
                    </div>
                    <div class="w-full">
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-3">És o dono dos teus dados</h2>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Não te prendemos à plataforma. Em total conformidade com o RGPD, podes descarregar a tua história completa ou pausar a tua conta a qualquer momento.
                        </p>
                        
                        <div class="grid sm:grid-cols-2 gap-4">
                            <form method="POST" action="{{ route('privacy.export') }}">
                                @csrf
                                <button type="submit" class="w-full bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 p-4 rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                                    <i class="ri-download-cloud-2-line"></i> Exportar Dados (JSON)
                                </button>
                            </form>

                            <a href="{{ route('profile.edit') }}" class="w-full bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 p-4 rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                                <i class="ri-zzz-line"></i> Hibernar Conta
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 text-slate-300 rounded-[2.5rem] p-8 md:p-10 shadow-2xl mt-8">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="ri-lock-password-line text-indigo-400"></i> Como protegemos a plataforma
                </h2>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="ri-check-line text-emerald-400 mt-0.5"></i>
                        <span><strong>Encriptação:</strong> As tuas palavras-passe e dados sensíveis são guardados com algoritmos de encriptação fortes padrão da indústria (Bcrypt).</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="ri-check-line text-emerald-400 mt-0.5"></i>
                        <span><strong>Nenhuma venda de dados:</strong> Nunca venderemos a tua informação a anunciantes, seguradoras ou terceiros. O nosso modelo não depende de explorar a tua dor.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="ri-check-line text-emerald-400 mt-0.5"></i>
                        <span><strong>Proteção de Crise:</strong> Os nossos sistemas podem acionar alertas se detetarem palavras relacionadas com risco iminente de vida, apenas para podermos oferecer-te ajuda imediata de moderadores clínicos.</span>
                    </li>
                </ul>
            </div>

        </div>
        
        <div class="mt-12 text-center">
            <a href="{{ route('dashboard') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-flex items-center gap-1">
                <i class="ri-arrow-left-line"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>

</x-lumina-layout>