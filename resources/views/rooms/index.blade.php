<x-lumina-layout title="A Fogueira | Comunidade Lumina">

    <div class="pt-12 pb-24 min-h-screen">
        <div class="max-w-7xl mx-auto px-6 mb-16 text-center animate-fade-up">
            <span class="inline-block py-1 px-3 rounded-full bg-orange-50 text-orange-600 text-xs font-bold uppercase tracking-wider mb-4 border border-orange-100">Grupos de Apoio</span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-6">Escolhe a tua <span class="text-orange-500">Fogueira</span></h1>
            <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed">
                Estas salas são espaços seguros, anónimos e moderados. Entra, ouve o que os outros têm a dizer e partilha quando te sentires pronto.
            </p>
        </div>

        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @forelse($rooms as $room)
                    @php
                        // Garante que se a cor vier vazia ou inválida da BD, não quebra o layout
                        $color = in_array($room->color, ['rose', 'emerald', 'blue', 'amber', 'indigo', 'orange', 'teal', 'violet']) ? $room->color : 'indigo';
                        $icon = $room->icon ?? 'ri-fire-fill';
                    @endphp

                    <a href="{{ route('chat.show', $room) }}" class="group relative bg-white/80 backdrop-blur-md rounded-3xl p-8 border border-white shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden cursor-pointer h-full flex flex-col block">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-{{ $color }}-50 rounded-bl-[100px] -mr-8 -mt-8 z-0 transition-transform group-hover:scale-110"></div>
                        
                        <div class="relative z-10 flex-grow">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-{{ $color }}-100 text-{{ $color }}-500 flex items-center justify-center text-2xl shadow-sm mb-6 group-hover:scale-110 transition-transform">
                                <i class="{{ $icon }}"></i>
                            </div>
                            
                            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $room->name }}</h3>
                            <p class="text-sm text-slate-500 leading-relaxed mb-6">{{ $room->description }}</p>
                        </div>

                        <div class="relative z-10 mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] text-slate-500 font-bold shadow-sm">A</div>
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[10px] text-slate-500 font-bold shadow-sm">B</div>
                                <div class="w-8 h-8 rounded-full border-2 border-white bg-{{ $color }}-100 flex items-center justify-center text-[10px] font-bold text-{{ $color }}-600 shadow-sm">+{{ rand(5, 20) }}</div>
                            </div>
                            
                            <span class="text-{{ $color }}-600 font-bold text-sm flex items-center gap-1 group-hover:underline">
                                Entrar na Roda <i class="ri-arrow-right-line"></i>
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-[2rem] border border-slate-100 shadow-sm">
                        <i class="ri-tent-line text-4xl text-slate-300 block mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-700">O acampamento está a ser montado</h3>
                        <p class="text-slate-500 mt-2">Ainda não existem salas disponíveis. Volta em breve.</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <a href="https://www.google.pt" class="fixed bottom-6 right-6 z-[60] bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full shadow-xl flex items-center gap-2 transition-transform hover:scale-105 border-4 border-white ring-2 ring-red-100" title="Sair rapidamente para o Google">
            <i class="ri-eye-off-line text-xl"></i> 
            <span class="hidden md:inline">Saída Rápida</span>
        </a>
    </x-slot>

</x-lumina-layout>