<x-lumina-layout title="Portal do Terapeuta | Lumina PRO">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Header Profissional --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-[10px] font-black uppercase tracking-widest mb-3 border border-teal-100 dark:border-teal-800">
                        <i class="ri-verified-badge-fill"></i> Conta Profissional
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white">A tua Clínica Digital</h1>
                    <p class="text-slate-500 text-sm mt-1">Bem-vindo(a), Dr(a). {{ Auth::user()->name ?? 'Terapeuta' }}</p>
                </div>
                
                <div class="flex gap-3">
                    <button class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <i class="ri-calendar-line"></i> Agenda
                    </button>
                    <button class="px-5 py-2.5 bg-slate-900 dark:bg-teal-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-slate-900/20 dark:shadow-teal-900/20 hover:bg-slate-800 dark:hover:bg-teal-500 transition-all">
                        <i class="ri-add-line"></i> Novo Paciente
                    </button>
                </div>
            </div>

            {{-- KPIs Clínicos --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl">
                        <i class="ri-group-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pacientes Ativos</p>
                        <p class="text-2xl font-black text-slate-800 dark:text-white">12</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                        <i class="ri-heart-pulse-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Atenção Necessária</p>
                        <p class="text-2xl font-black text-rose-600 dark:text-rose-400">2 <span class="text-xs font-medium text-slate-500">pacientes em baixo</span></p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl">
                        <i class="ri-check-double-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Missões Concluídas</p>
                        <p class="text-2xl font-black text-slate-800 dark:text-white">85%</p>
                    </div>
                </div>
            </div>

            {{-- Lista de Pacientes --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Os teus Pacientes</h2>
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Procurar paciente..." class="pl-9 pr-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 outline-none w-64">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white dark:bg-slate-800 text-xs text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-700">
                                <th class="p-4 font-bold">Paciente</th>
                                <th class="p-4 font-bold">Clima da Alma (7 dias)</th>
                                <th class="p-4 font-bold">Última Sessão</th>
                                <th class="p-4 font-bold text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            {{-- Paciente Exemplo 1 (Atenção) --}}
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-slate-600 dark:text-slate-300">MR</div>
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-white text-sm">Miguel Ribeiro</p>
                                            <p class="text-xs text-slate-500">Ansiedade Generalizada</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-heavy-showers-line text-rose-500 text-lg"></i>
                                        <span class="text-sm font-bold text-rose-600 dark:text-rose-400">Chuvoso</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-400">Há 3 dias</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button class="p-2 text-slate-400 hover:text-indigo-600 bg-slate-50 dark:bg-slate-900 rounded-lg transition-colors" title="Ver Passaporte Emocional">
                                            <i class="ri-folder-shield-2-line"></i>
                                        </button>
                                        <button class="px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg text-xs font-bold hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-colors" onclick="openMissionModal('Miguel')">
                                            Prescrever Missão
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Paciente Exemplo 2 (Estável) --}}
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-slate-600 dark:text-slate-300">AS</div>
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-white text-sm">Ana Silva</p>
                                            <p class="text-xs text-slate-500">Gestão de Stress</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-sun-line text-amber-500 text-lg"></i>
                                        <span class="text-sm font-bold text-amber-600 dark:text-amber-400">Ensolarado</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-400">Ontem</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button class="p-2 text-slate-400 hover:text-indigo-600 bg-slate-50 dark:bg-slate-900 rounded-lg transition-colors" title="Ver Passaporte Emocional">
                                            <i class="ri-folder-shield-2-line"></i>
                                        </button>
                                        <button class="px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg text-xs font-bold hover:bg-teal-100 dark:hover:bg-teal-900/50 transition-colors" onclick="openMissionModal('Ana')">
                                            Prescrever Missão
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Simulador: Prescrever Missão --}}
    <div id="missionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('missionModal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl w-full max-w-md animate-fade-up">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Prescrever Missão</h3>
            <p id="missionPatientName" class="text-sm text-slate-500 mb-6"></p>

            <div class="space-y-4">
                <label class="cursor-pointer flex items-center gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <input type="radio" name="mission" class="text-teal-500 focus:ring-teal-500">
                    <div>
                        <p class="font-bold text-sm text-slate-800 dark:text-white">Diário de Combustão</p>
                        <p class="text-xs text-slate-500">Pedir para processar uma frustração hoje.</p>
                    </div>
                </label>
                <label class="cursor-pointer flex items-center gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <input type="radio" name="mission" class="text-teal-500 focus:ring-teal-500">
                    <div>
                        <p class="font-bold text-sm text-slate-800 dark:text-white">Respiração Cega (3 min)</p>
                        <p class="text-xs text-slate-500">Exercício somático para reduzir picos de ansiedade.</p>
                    </div>
                </label>
            </div>

            <div class="mt-6 flex gap-3">
                <button onclick="document.getElementById('missionModal').classList.add('hidden')" class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Cancelar</button>
                <button onclick="alert('Missão enviada com sucesso para o paciente!'); document.getElementById('missionModal').classList.add('hidden')" class="flex-1 py-3 bg-teal-600 text-white text-sm font-bold rounded-xl hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/20">Enviar ao Paciente</button>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function openMissionModal(patientName) {
                document.getElementById('missionPatientName').innerText = 'Paciente: ' + patientName;
                document.getElementById('missionModal').classList.remove('hidden');
            }
        </script>
    </x-slot>
</x-lumina-layout>