<x-lumina-layout title="Videochamada | Lumina">
    <div class="pt-16 h-screen flex flex-col bg-slate-950">

        {{-- Barra superior minimalista --}}
        <div class="flex items-center justify-between px-4 py-3 bg-slate-900 border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-white text-sm font-bold">Sessão em curso</span>
                <span class="text-slate-400 text-xs hidden md:inline">
                    {{ $session->scheduled_at->translatedFormat('d \d\e F — H:i') }}
                    · {{ $session->duration_minutes }} minutos
                </span>
            </div>
            <a href="{{ route('sessions.index') }}"
                class="px-4 py-2 text-sm font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition-colors min-h-[44px] flex items-center gap-1.5">
                <i class="ri-logout-box-r-line"></i>
                <span class="hidden sm:inline">Sair da sessão</span>
            </a>
        </div>

        {{-- Container Jitsi a ocupar o espaço restante --}}
        <div id="jitsi-container" class="flex-1 w-full"></div>
    </div>

    <x-slot name="scripts">
        <script src="https://{{ $jitsiDomain }}/external_api.js"></script>
        <script>
            const domain = @json($jitsiDomain);
            const roomName = @json($session->video_room_token);
            const displayName = @json($userName);

            const options = {
                roomName: roomName,
                parentNode: document.getElementById('jitsi-container'),
                width: '100%',
                height: '100%',
                userInfo: { displayName: displayName },
                configOverwrite: {
                    // Desativa funcionalidades não necessárias para um ambiente clínico
                    disableDeepLinking: true,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    // Remove branding Jitsi para ambiente clínico neutro
                    brandingRoomAlias: null,
                },
                interfaceConfigOverwrite: {
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'chat', 'settings', 'videoquality', 'tileview',
                    ],
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_BACKGROUND: '#0f172a',
                },
            };

            const api = new JitsiMeetExternalAPI(domain, options);

            // Regista o fim da chamada para redirecionar o utilizador
            api.addEventListener('readyToClose', () => {
                window.location.href = @json(route('sessions.index'));
            });
        </script>
    </x-slot>
</x-lumina-layout>
