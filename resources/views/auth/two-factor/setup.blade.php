<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configurar Autenticação de Dois Fatores
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">

                <p class="text-gray-700">
                    A tua conta requer autenticação de dois fatores. Digitaliza o QR Code abaixo com a tua aplicação autenticadora (ex: Google Authenticator, Authy) e introduz o código de 6 dígitos para ativar.
                </p>

                {{-- QR Code gerado pelo google2fa-qrcode --}}
                <div class="flex justify-center">
                    {!! QrCode::size(200)->generate($qrCodeUrl) !!}
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="code" value="Código de verificação" />
                        <x-text-input
                            id="code"
                            name="code"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            class="mt-1 block w-full"
                            placeholder="000000"
                            autofocus
                        />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        Ativar 2FA
                    </x-primary-button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
