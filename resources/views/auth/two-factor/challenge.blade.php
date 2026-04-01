<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Verificação de Dois Fatores
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">

                <p class="text-gray-700">
                    Introduz o código de 6 dígitos gerado pela tua aplicação autenticadora para continuar.
                </p>

                <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="code" value="Código de autenticação" />
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
                        Verificar
                    </x-primary-button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
