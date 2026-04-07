<x-lumina-layout title="Integrações HR | Lumina PRO">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 text-[10px] font-black uppercase tracking-widest mb-3 border border-violet-100 dark:border-violet-800">
                        <i class="ri-plug-line"></i> INOV-12 · Integrações HR
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white">Integrações HR</h1>
                    <p class="text-slate-500 text-sm mt-1">Sincroniza colaboradores via SAP, Workday ou sistema genérico</p>
                </div>
                <a href="{{ route('corporate.dashboard') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-50 transition-colors min-h-[44px] flex items-center gap-2">
                    <i class="ri-arrow-left-line"></i> Dashboard
                </a>
            </div>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm font-medium">
                    <i class="ri-checkbox-circle-line mr-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- Adicionar integração --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Nova Integração</h2>
                <p class="text-sm text-slate-500 mb-5">
                    Configura a receção de webhooks do teu sistema HR. O endpoint gerado abaixo deve ser registado na plataforma SAP/Workday.
                </p>
                <form method="POST" action="{{ route('hr.integrations.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-white mb-1">Sistema HR</label>
                            <select name="provider" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm p-3 focus:ring-2 focus:ring-violet-500 outline-none min-h-[44px]">
                                <option value="generic">Genérico (REST)</option>
                                <option value="sap">SAP SuccessFactors</option>
                                <option value="workday">Workday</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-white mb-1">
                                URL de Callback <span class="font-normal text-slate-400">(opcional)</span>
                            </label>
                            <input type="url" name="webhook_url" placeholder="https://sistema-hr.empresa.pt/lumina-callback"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm p-3 focus:ring-2 focus:ring-violet-500 outline-none min-h-[44px]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-white mb-2">Eventos a subscrever</label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="event_types[]" value="employee.created" checked class="rounded text-violet-600 focus:ring-violet-500 w-4 h-4">
                                <span class="text-sm text-slate-700 dark:text-slate-300">Colaborador criado</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="event_types[]" value="employee.terminated" checked class="rounded text-violet-600 focus:ring-violet-500 w-4 h-4">
                                <span class="text-sm text-slate-700 dark:text-slate-300">Colaborador desligado</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="px-6 py-2.5 bg-violet-600 text-white text-sm font-bold rounded-xl hover:bg-violet-700 transition-colors shadow-lg shadow-violet-600/20 min-h-[44px]">
                        <i class="ri-save-line mr-1"></i> Guardar Integração
                    </button>
                </form>
            </div>

            {{-- Integrações configuradas --}}
            @if ($configs->isNotEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Integrações Ativas</h2>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($configs as $config)
                            <div class="p-5" x-data="{ showToken: false }">
                                <div class="flex flex-col md:flex-row md:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-slate-800 dark:text-white text-sm capitalize">{{ $config->provider }}</span>
                                            @if ($config->is_active)
                                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Ativo
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500">
                                            Eventos: {{ implode(', ', $config->event_types) }}
                                            · {{ $config->total_events }} recebidos
                                            @if ($config->failed_events > 0)
                                                · <span class="text-rose-500 font-bold">{{ $config->failed_events }} falhas</span>
                                            @endif
                                        </p>
                                        {{-- Endpoint para configurar no sistema HR --}}
                                        <div class="mt-2 flex items-center gap-2">
                                            <code class="text-xs bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 px-2 py-1 rounded-lg">
                                                POST {{ url('/api/hr-webhook/' . $company->slug) }}
                                            </code>
                                            <button type="button" @click="showToken = !showToken" class="text-xs text-violet-600 dark:text-violet-400 hover:underline">
                                                {{ '{{ showToken ? "Esconder" : "Ver token" }}' }}
                                            </button>
                                        </div>
                                        {{-- Token secreto — oculto por defeito --}}
                                        <div x-show="showToken" x-cloak class="mt-2">
                                            <code class="text-xs bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 px-3 py-1.5 rounded-lg block break-all border border-amber-200 dark:border-amber-800">
                                                ⚠ Guarda este token com segurança — não será mostrado novamente após regeneração.
                                            </code>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('hr.integrations.destroy', $config) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-4 py-2 text-rose-500 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 text-sm font-bold rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors min-h-[44px]"
                                            onclick="return confirm('Remover esta integração? Os logs serão mantidos.')">
                                            <i class="ri-delete-bin-line"></i> Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Logs recentes --}}
            @if ($recentLogs->isNotEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Eventos Recentes</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-700">
                                <tr>
                                    <th class="p-4">Evento</th>
                                    <th class="p-4">Provider</th>
                                    <th class="p-4">Estado</th>
                                    <th class="p-4">Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach ($recentLogs as $log)
                                    @php
                                        $statusColor = match($log->status) {
                                            'processed' => 'emerald',
                                            'failed' => 'rose',
                                            'ignored' => 'slate',
                                            default => 'amber',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="p-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $log->event_type }}</td>
                                        <td class="p-4 text-slate-500 capitalize">{{ $log->provider }}</td>
                                        <td class="p-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $statusColor }}-50 dark:bg-{{ $statusColor }}-900/30 text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400">
                                                {{ $log->status }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-slate-500 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-lumina-layout>
