<x-lumina-layout title="As Minhas Sessões | Lumina">
    <div class="py-12 pt-28 md:pt-32">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-[10px] font-black uppercase tracking-widest mb-3 border border-teal-100 dark:border-teal-800">
                        <i class="ri-calendar-heart-line"></i> Sessões Terapêuticas
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white">As Minhas Sessões</h1>
                    <p class="text-slate-500 text-sm mt-1">Consulta e gere os teus agendamentos</p>
                </div>
            </div>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm font-medium">
                    <i class="ri-checkbox-circle-line mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($sessions->isEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-500 flex items-center justify-center text-3xl mx-auto mb-4">
                        <i class="ri-calendar-line"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700 dark:text-white mb-2">Sem sessões agendadas</h3>
                    <p class="text-slate-500 text-sm">Visita o perfil do teu terapeuta para agendar a primeira sessão.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($sessions as $session)
                        @php
                            $statusColor = match($session->status) {
                                'confirmed' => 'emerald',
                                'pending' => 'amber',
                                'cancelled' => 'rose',
                                'completed' => 'slate',
                                'no_show' => 'rose',
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
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 flex flex-col md:flex-row md:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $statusColor }}-50 dark:bg-{{ $statusColor }}-900/30 text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400 border border-{{ $statusColor }}-100 dark:border-{{ $statusColor }}-800">
                                        {{ $statusLabel }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ $session->session_type === 'video' ? '📹 Vídeo' : '🏢 Presencial' }}</span>
                                </div>
                                <p class="font-bold text-slate-800 dark:text-white">
                                    {{ $session->scheduled_at->translatedFormat('l, d \d\e F \d\e Y — H:i') }}
                                </p>
                                <p class="text-sm text-slate-500 mt-0.5">
                                    Dr(a). {{ $session->therapist->name ?? $session->therapist->user->name }}
                                    · {{ $session->duration_minutes }} minutos
                                </p>
                            </div>

                            <div class="flex items-center gap-2 flex-shrink-0">
                                {{-- Botão de videochamada — disponível 10 min antes --}}
                                @if ($session->status === 'confirmed' && $session->session_type === 'video' && $session->isVideoCallAccessible())
                                    <a href="{{ route('sessions.video', $session) }}"
                                        class="px-4 py-2 bg-teal-600 text-white text-sm font-bold rounded-xl hover:bg-teal-700 transition-colors shadow-lg shadow-teal-600/20 flex items-center gap-2 min-h-[44px]">
                                        <i class="ri-video-chat-line"></i> Entrar
                                    </a>
                                @endif

                                {{-- Cancelar — apenas sessões futuras não canceladas --}}
                                @if (in_array($session->status, ['pending', 'confirmed']) && $session->scheduled_at->isFuture())
                                    <button
                                        x-data
                                        @click="$dispatch('open-cancel-modal', { id: {{ $session->id }} })"
                                        class="px-4 py-2 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800 text-sm font-bold rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors min-h-[44px]">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de cancelamento --}}
    <div
        x-data="{ open: false, sessionId: null }"
        @open-cancel-modal.window="open = true; sessionId = $event.detail.id"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-2xl w-full max-w-md">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Cancelar Sessão</h3>
            <p class="text-sm text-slate-500 mb-5">Indica o motivo do cancelamento (opcional).</p>
            <form method="POST" :action="`/sessoes/${sessionId}/cancelar`">
                @csrf
                @method('PATCH')
                <textarea name="cancellation_reason" rows="3" placeholder="Motivo..."
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm p-3 focus:ring-2 focus:ring-teal-500 outline-none resize-none"></textarea>
                <div class="mt-4 flex gap-3">
                    <button type="button" @click="open = false"
                        class="flex-1 py-3 text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Voltar
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 bg-rose-500 text-white text-sm font-bold rounded-xl hover:bg-rose-600 transition-colors">
                        Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-lumina-layout>
