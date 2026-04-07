<x-lumina-layout title="Agenda | Portal do Terapeuta | Lumina PRO">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-[10px] font-black uppercase tracking-widest mb-3 border border-teal-100 dark:border-teal-800">
                        <i class="ri-calendar-schedule-line"></i> Gestão de Agenda
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white">Agenda</h1>
                    <p class="text-slate-500 text-sm mt-1">Sessões agendadas e disponibilidade semanal</p>
                </div>
                <a href="{{ route('therapist.dashboard') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-50 transition-colors min-h-[44px] flex items-center gap-2">
                    <i class="ri-arrow-left-line"></i> Dashboard
                </a>
            </div>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm font-medium">
                    <i class="ri-checkbox-circle-line mr-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- Sessões --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Sessões Agendadas</h2>
                </div>

                @if ($sessions->isEmpty())
                    <div class="p-10 text-center text-slate-500 text-sm">
                        <i class="ri-calendar-line text-3xl mb-2 block text-slate-300"></i>
                        Ainda não tens sessões agendadas.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($sessions as $session)
                            @php
                                $statusColor = match($session->status) {
                                    'confirmed' => 'emerald',
                                    'pending' => 'amber',
                                    'cancelled' => 'rose',
                                    'completed' => 'slate',
                                    default => 'slate',
                                };
                                $statusLabel = match($session->status) {
                                    'confirmed' => 'Confirmada',
                                    'pending' => 'Pendente',
                                    'cancelled' => 'Cancelada',
                                    'completed' => 'Concluída',
                                    'no_show' => 'Não compareceu',
                                    default => $session->status,
                                };
                            @endphp
                            <div class="p-5 flex flex-col md:flex-row md:items-center gap-4" x-data="{ showCancel: false }">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $statusColor }}-50 dark:bg-{{ $statusColor }}-900/30 text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400 border border-{{ $statusColor }}-100 dark:border-{{ $statusColor }}-800">
                                            {{ $statusLabel }}
                                        </span>
                                        <span class="text-xs text-slate-400">{{ $session->session_type === 'video' ? '📹 Vídeo' : '🏢 Presencial' }}</span>
                                    </div>
                                    <p class="font-bold text-slate-800 dark:text-white">
                                        {{ $session->scheduled_at->translatedFormat('l, d \d\e F \d\e Y — H:i') }}
                                    </p>
                                    <p class="text-sm text-slate-500">{{ $session->patient->name }} · {{ $session->duration_minutes }} min</p>
                                    @if ($session->patient_notes)
                                        <p class="text-xs text-slate-400 mt-1 italic">"{{ Str::limit($session->patient_notes, 80) }}"</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if ($session->status === 'pending')
                                        <form method="POST" action="{{ route('therapist.schedule.confirm', $session) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors min-h-[44px]">
                                                <i class="ri-check-line"></i> Confirmar
                                            </button>
                                        </form>
                                    @endif

                                    @if ($session->status === 'confirmed' && $session->session_type === 'video' && $session->isVideoCallAccessible())
                                        <a href="{{ route('sessions.video', $session) }}"
                                            class="px-4 py-2 bg-teal-600 text-white text-sm font-bold rounded-xl hover:bg-teal-700 transition-colors min-h-[44px] flex items-center gap-2">
                                            <i class="ri-video-chat-line"></i> Entrar
                                        </a>
                                    @endif

                                    @if ($session->status === 'confirmed')
                                        <form method="POST" action="{{ route('therapist.schedule.complete', $session) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors min-h-[44px]">
                                                Concluir
                                            </button>
                                        </form>
                                    @endif

                                    @if (in_array($session->status, ['pending', 'confirmed']))
                                        <button @click="showCancel = !showCancel" class="px-3 py-2 text-rose-500 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 text-sm font-bold rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors min-h-[44px]">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    @endif
                                </div>

                                @if (in_array($session->status, ['pending', 'confirmed']))
                                    <div x-show="showCancel" x-cloak class="w-full mt-2">
                                        <form method="POST" action="{{ route('therapist.schedule.cancel', $session) }}" class="flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="cancellation_reason" placeholder="Motivo do cancelamento (opcional)"
                                                class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm p-2.5 focus:ring-2 focus:ring-rose-400 outline-none">
                                            <button type="submit" class="px-4 py-2 bg-rose-500 text-white text-sm font-bold rounded-xl hover:bg-rose-600 transition-colors min-h-[44px]">
                                                Cancelar sessão
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Disponibilidade Semanal --}}
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Disponibilidade Semanal</h2>
                    <p class="text-sm text-slate-500 mt-1">Define os teus horários disponíveis para agendamento</p>
                </div>

                <div class="p-6" x-data="availabilityManager({{ $availability->toJson() }})">
                    <form method="POST" action="{{ route('therapist.schedule.availability') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-3" id="slots-container">
                            <template x-for="(slot, index) in slots" :key="index">
                                <div class="flex flex-wrap items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                                    <select :name="`slots[${index}][day_of_week]`" x-model="slot.day_of_week"
                                        class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm p-2.5 focus:ring-2 focus:ring-teal-500 outline-none min-h-[44px]">
                                        <option value="1">Segunda-feira</option>
                                        <option value="2">Terça-feira</option>
                                        <option value="3">Quarta-feira</option>
                                        <option value="4">Quinta-feira</option>
                                        <option value="5">Sexta-feira</option>
                                        <option value="6">Sábado</option>
                                        <option value="0">Domingo</option>
                                    </select>
                                    <span class="text-sm text-slate-400">das</span>
                                    <input type="time" :name="`slots[${index}][start_time]`" x-model="slot.start_time"
                                        class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm p-2.5 focus:ring-2 focus:ring-teal-500 outline-none min-h-[44px]">
                                    <span class="text-sm text-slate-400">às</span>
                                    <input type="time" :name="`slots[${index}][end_time]`" x-model="slot.end_time"
                                        class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm p-2.5 focus:ring-2 focus:ring-teal-500 outline-none min-h-[44px]">
                                    <button type="button" @click="removeSlot(index)"
                                        class="p-2.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-colors min-h-[44px] min-w-[44px]">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="button" @click="addSlot()"
                                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors min-h-[44px] flex items-center gap-2">
                                <i class="ri-add-line"></i> Adicionar Horário
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 bg-teal-600 text-white text-sm font-bold rounded-xl hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/20 min-h-[44px]">
                                Guardar Disponibilidade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function availabilityManager(initial) {
                return {
                    slots: initial.length ? initial.map(s => ({
                        day_of_week: String(s.day_of_week),
                        start_time: s.start_time.substring(0, 5),
                        end_time: s.end_time.substring(0, 5),
                    })) : [],
                    addSlot() {
                        this.slots.push({ day_of_week: '1', start_time: '09:00', end_time: '17:00' });
                    },
                    removeSlot(index) {
                        this.slots.splice(index, 1);
                    }
                };
            }
        </script>
    </x-slot>
</x-lumina-layout>
