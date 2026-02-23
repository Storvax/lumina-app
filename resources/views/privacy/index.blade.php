<x-lumina-layout title="Transparência e Privacidade | Lumina">

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none bg-[#F8FAFC] dark:bg-slate-900 transition-colors duration-500">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-200/30 dark:bg-indigo-900/20 rounded-full blur-[100px] mix-blend-multiply dark:mix-blend-lighten"></div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-12 pt-32">
        
        <header class="text-center mb-16">
            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-sm">
                <i class="ri-shield-check-fill" aria-hidden="true"></i>
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
                        <i class="ri-book-3-line" aria-hidden="true"></i>
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
                        <i class="ri-team-line" aria-hidden="true"></i>
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
                    <div class="w-14 h-14 bg-purple-50 dark:bg-purple-900/30 text-purple-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-history-line" aria-hidden="true"></i>
                    </div>
                    <div class="w-full">
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-3">Auditoria de Acessos (RGPD)</h2>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Em nome da transparência radical, listamos abaixo as vezes em que a nossa equipa clínica ou de moderação precisou de visualizar o teu conteúdo para garantir a tua segurança ou rever uma denúncia.
                        </p>

                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 max-h-72 overflow-y-auto custom-scrollbar">
                            <ul class="space-y-4">
                                @forelse($auditLogs ?? [] as $log)
                                    <li class="text-sm text-slate-600 dark:text-slate-300 border-l-2 border-purple-300 dark:border-purple-600 pl-4 py-1">
                                        <span class="font-bold text-slate-800 dark:text-white">A Equipa de Suporte</span> visualizou um <span class="uppercase text-[10px] font-bold bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md">{{ str_replace('_', ' ', $log->data_type) }}</span> teu.<br>
                                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 text-xs text-slate-500 dark:text-slate-400">
                                            <span class="flex items-center gap-1"><i class="ri-calendar-line"></i> {{ $log->created_at->format('d/m/Y \à\s H:i') }}</span>
                                            <span class="hidden sm:inline">•</span>
                                            <span class="flex items-center gap-1"><i class="ri-information-line"></i> Motivo: {{ $log->purpose }}</span>
                                        </div>
                                    </li>
                                @empty
                                    <div class="text-center py-6">
                                        <i class="ri-shield-keyhole-line text-4xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Os teus dados sensíveis nunca foram acedidos por terceiros.</p>
                                    </div>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-slate-200/40 border border-slate-100 dark:border-slate-700">
                <div class="flex items-start gap-6">
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/30 text-amber-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-database-2-line" aria-hidden="true"></i>
                    </div>
                    <div class="w-full">
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-3">És o dono dos teus dados</h2>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Não te prendemos à plataforma. Em total conformidade com o RGPD, podes descarregar a tua história completa ou pausar a tua conta a qualquer momento.
                        </p>
                        
                        <div class="grid sm:grid-cols-2 gap-4">
                            <form method="POST" action="{{ route('privacy.export') }}">
                                @csrf
                                <button type="submit" class="w-full bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 p-4 rounded-xl font-bold transition-colors flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-slate-400 outline-none">
                                    <i class="ri-download-cloud-2-line" aria-hidden="true"></i> Exportar Dados (JSON)
                                </button>
                            </form>

                            <a href="{{ route('profile.edit') }}" class="w-full bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 p-4 rounded-xl font-bold transition-colors flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-amber-400 outline-none">
                                <i class="ri-zzz-line" aria-hidden="true"></i> Hibernar Conta
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 text-slate-300 rounded-[2.5rem] p-8 md:p-10 shadow-2xl mt-8">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="ri-lock-password-line text-indigo-400" aria-hidden="true"></i> Como protegemos a plataforma
                </h2>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="ri-check-line text-emerald-400 mt-0.5" aria-hidden="true"></i>
                        <span><strong>Encriptação:</strong> As tuas palavras-passe e dados sensíveis são guardados com algoritmos de encriptação fortes padrão da indústria (Bcrypt). As tuas mensagens diretas são encriptadas na base de dados.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="ri-check-line text-emerald-400 mt-0.5" aria-hidden="true"></i>
                        <span><strong>Nenhuma venda de dados:</strong> Nunca venderemos a tua informação a anunciantes, seguradoras ou terceiros. O nosso modelo não depende de explorar a tua dor.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="ri-check-line text-emerald-400 mt-0.5" aria-hidden="true"></i>
                        <span><strong>Proteção de Crise:</strong> Os nossos sistemas podem acionar alertas se detetarem palavras relacionadas com risco iminente de vida, apenas para podermos oferecer-te ajuda imediata de moderadores clínicos.</span>
                    </li>
                </ul>
            </div>

        </div>
        
        <div class="mt-12 text-center">
            <a href="{{ route('dashboard') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-flex items-center gap-1 focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none rounded-md px-2 py-1">
                <i class="ri-arrow-left-line" aria-hidden="true"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>

</x-lumina-layout>