<x-lumina-layout title="Gestão de Colaboradores | Lumina PRO">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest mb-3 border border-indigo-100 dark:border-indigo-800">
                        <i class="ri-group-line"></i> Gestão RH
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white">Colaboradores</h1>
                    <p class="text-slate-500 text-sm mt-1">{{ $company->name }} · {{ $employees->count() }} colaboradores</p>
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

            @if ($errors->any())
                <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-600 dark:text-rose-300 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Convidar colaborador --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Convidar Colaborador</h2>
                <p class="text-sm text-slate-500 mb-5">Envia um convite por email. O convite é válido por 7 dias.</p>
                <form method="POST" action="{{ route('company.invite') }}" class="flex flex-wrap gap-3">
                    @csrf
                    <input type="email" name="email" placeholder="email@empresa.pt" required
                        class="flex-1 min-w-[200px] rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm p-3 focus:ring-2 focus:ring-indigo-500 outline-none min-h-[44px]">
                    <select name="role" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 text-sm p-3 focus:ring-2 focus:ring-indigo-500 outline-none min-h-[44px]">
                        <option value="employee">Colaborador</option>
                        <option value="hr_admin">Administrador RH</option>
                    </select>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/20 min-h-[44px]">
                        <i class="ri-mail-send-line mr-1"></i> Enviar Convite
                    </button>
                </form>
            </div>

            {{-- Lista de colaboradores --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Colaboradores Ativos</h2>
                </div>
                @if ($employees->isEmpty())
                    <div class="p-10 text-center text-slate-500 text-sm">
                        <i class="ri-group-line text-3xl mb-2 block text-slate-300"></i>
                        Nenhum colaborador associado ainda.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($employees as $employee)
                            <div class="flex items-center gap-4 p-4">
                                <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-slate-600 dark:text-slate-300 text-sm flex-shrink-0">
                                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-800 dark:text-white text-sm truncate">{{ $employee->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $employee->email }}</p>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $employee->company_role === 'hr_admin' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                                    {{ $employee->company_role === 'hr_admin' ? 'Admin RH' : 'Colaborador' }}
                                </span>
                                @if ($employee->id !== Auth::id())
                                    <form method="POST" action="{{ route('company.employees.remove') }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                        <button type="submit" class="p-2 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                                            onclick="return confirm('Remover este colaborador da empresa?')">
                                            <i class="ri-user-unfollow-line"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Convites pendentes --}}
            @if ($invitations->isNotEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Convites</h2>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($invitations as $inv)
                            <div class="flex items-center gap-4 p-4">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-white text-sm truncate">{{ $inv->email }}</p>
                                    <p class="text-xs text-slate-500">Enviado {{ $inv->created_at->diffForHumans() }}</p>
                                </div>
                                @if ($inv->accepted_at)
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">Aceite</span>
                                @elseif ($inv->isExpired())
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500">Expirado</span>
                                @else
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">Pendente</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-lumina-layout>
