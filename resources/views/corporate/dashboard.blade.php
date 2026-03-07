<x-lumina-layout title="Portal da Empresa | Lumina Corporate">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Header Corporativo --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest mb-3 border border-slate-200 dark:border-slate-700">
                        <i class="ri-building-4-line"></i> Dashboard de Recursos Humanos
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                        TechCorp S.A. <i class="ri-verified-badge-fill text-blue-500 text-2xl"></i>
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Visão geral do bem-estar da sua equipa (Dados 100% Anónimos e Agregados).</p>
                </div>
                
                <div class="flex gap-3">
                    <button class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <i class="ri-download-2-line"></i> Relatório Mensal
                    </button>
                    <button class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all">
                        <i class="ri-user-add-line"></i> Convidar Colaboradores
                    </button>
                </div>
            </div>

            {{-- Alerta SOS (Aparece apenas se houver risco detetado pela IA) --}}
            <div class="bg-rose-50 border border-rose-200 dark:bg-rose-900/20 dark:border-rose-900/50 rounded-2xl p-6 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shrink-0 animate-pulse">
                        <i class="ri-alarm-warning-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-rose-800 dark:text-rose-300 font-bold text-lg">Alerta de Risco de Burnout</h3>
                        <p class="text-rose-600/80 dark:text-rose-400/80 text-sm mt-1">A IA da Lumina detetou um aumento de 40% em tags de "Exaustão" e "Ansiedade" no departamento de <strong>Vendas</strong> nos últimos 7 dias.</p>
                    </div>
                </div>
                <button class="shrink-0 px-5 py-2.5 bg-rose-600 text-white rounded-xl text-sm font-bold shadow-sm hover:bg-rose-700 transition-colors">
                    Agendar Workshop de Alívio
                </button>
            </div>

            {{-- KPIs Globais --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Adoção --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                    <i class="ri-group-line absolute -right-4 -bottom-4 text-6xl text-slate-50 dark:text-slate-700/50"></i>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Taxa de Adoção</p>
                    <div class="flex items-end gap-3">
                        <p class="text-4xl font-black text-slate-800 dark:text-white">82%</p>
                        <p class="text-sm font-bold text-emerald-500 mb-1 flex items-center"><i class="ri-arrow-up-line"></i> 5%</p>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">164 de 200 colaboradores ativos na app.</p>
                </div>

                {{-- Clima Organizacional --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                    <i class="ri-sun-cloudy-line absolute -right-4 -bottom-4 text-6xl text-slate-50 dark:text-slate-700/50"></i>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Clima da Empresa (30 dias)</p>
                    <div class="flex items-end gap-3">
                        <p class="text-4xl font-black text-amber-500">Parcial. Nublado</p>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Média de humor: 3.4/5. Ligeira quebra face ao mês anterior.</p>
                </div>

                {{-- Sessões Clínicas --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm relative overflow-hidden">
                    <i class="ri-stethoscope-line absolute -right-4 -bottom-4 text-6xl text-slate-50 dark:text-slate-700/50"></i>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Consultas Psicológicas</p>
                    <div class="flex items-end gap-3">
                        <p class="text-4xl font-black text-teal-600 dark:text-teal-400">28</p>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Sessões realizadas este mês (cobertas pelo plano da empresa).</p>
                </div>
            </div>

            {{-- Distribuição Emocional (Gráfico Simulado) --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 md:p-8 border border-slate-100 dark:border-slate-700 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Distribuição de Humor (Semana Atual)</h3>
                
                <div class="space-y-4">
                    {{-- Barra Sunny --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-amber-500 flex items-center gap-1"><i class="ri-sun-fill"></i> Positivo / Ensolarado</span>
                            <span class="text-slate-500">60%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3">
                            <div class="bg-amber-400 h-3 rounded-full" style="width: 60%"></div>
                        </div>
                    </div>
                    
                    {{-- Barra Cloudy --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-500 flex items-center gap-1"><i class="ri-cloud-fill"></i> Neutro / Nublado</span>
                            <span class="text-slate-500">25%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3">
                            <div class="bg-slate-400 h-3 rounded-full" style="width: 25%"></div>
                        </div>
                    </div>

                    {{-- Barra Rainy --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-blue-500 flex items-center gap-1"><i class="ri-heavy-showers-fill"></i> Negativo / Chuvoso</span>
                            <span class="text-slate-500">15%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3">
                            <div class="bg-blue-400 h-3 rounded-full" style="width: 15%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 text-sm text-slate-500 flex items-start gap-3">
                    <i class="ri-shield-check-fill text-emerald-500 text-lg"></i>
                    <p>Para proteger o anonimato dos seus colaboradores, a Lumina apenas apresenta dados agregados quando existem pelo menos 5 registos num departamento. Os dados individuais são estritamente confidenciais e protegidos por RGPD.</p>
                </div>
            </div>

        </div>
    </div>
</x-lumina-layout>