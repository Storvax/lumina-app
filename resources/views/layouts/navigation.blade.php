<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('forum.index')" :active="request()->routeIs('forum.*')">
                        Mural
                    </x-nav-link>
                    <x-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">
                        Fogueira
                    </x-nav-link>
                    <x-nav-link :href="route('calm.index')" :active="request()->routeIs('calm.*')">
                        Zona Calmas
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                <a href="{{ route('calm.crisis') }}" class="mr-6 flex items-center gap-2 px-4 py-1.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl hover:bg-rose-100 transition-all font-bold text-sm">
                    <i class="ri-alarm-warning-line text-lg"></i>
                    <span>Modo Crise</span>
                </a>

                <div class="relative mr-4" x-data="{ open: false, hasNew: {{ Auth::user()->unreadNotifications->count() > 0 ? 'true' : 'false' }} }" x-on:new-notification.window="hasNew = true">
                    <button @click="open = !open; if(open) { axios.post('{{ route('notifications.read') }}'); hasNew = false; }" 
                            class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none">
                        <i class="ri-notification-3-line text-xl"></i>
                        
                        <span x-show="hasNew" x-cloak
                              class="absolute top-1 right-2 w-2 h-2 bg-rose-400 rounded-full border border-white animate-pulse">
                        </span>
                    </button>
                
                    <div x-show="open" 
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 top-full mt-2 w-[90vw] max-w-sm sm:w-80 bg-white rounded-3xl shadow-2xl shadow-indigo-900/10 border border-slate-100 z-50 overflow-hidden py-2"
                         style="display: none;">
                        
                        <div class="px-5 py-4 border-b border-slate-50 flex justify-between items-center">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">A tua comunidade</h3>
                            <button @click="open = false" class="text-slate-300 hover:text-rose-400 transition-colors"><i class="ri-close-line"></i></button>
                        </div>
                
                        <div class="max-h-80 overflow-y-auto custom-scrollbar">
                            @forelse(Auth::user()->notifications()->latest()->take(10)->get() as $notification)
                                @php $data = $notification->data; @endphp
                                <a href="{{ isset($data['post_id']) ? route('forum.show', $data['post_id']) : '#' }}" class="block px-5 py-4 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 {{ $notification->read_at ? 'opacity-60' : 'bg-indigo-50/20' }}">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-2xl {{ $data['color'] ?? 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-lg shrink-0">
                                            <i class="{{ $data['icon'] ?? 'ri-notification-line' }}"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-700 leading-snug">{{ $data['message'] ?? 'Nova interação' }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-5 py-8 text-center">
                                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300"><i class="ri-leaf-line text-xl"></i></div>
                                    <p class="text-sm font-medium text-slate-500">O silêncio também é bom.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.show')">
                            Ver Perfil
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">
                            Definições
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center gap-2 sm:hidden">
                <a href="{{ route('calm.crisis') }}" class="flex items-center justify-center w-9 h-9 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg hover:bg-rose-100 transition-all">
                    <i class="ri-alarm-warning-line text-lg"></i>
                </a>

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-100 shadow-lg absolute w-full z-40">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('forum.index')" :active="request()->routeIs('forum.*')">
                Mural
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')">
                Fogueira
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('calm.index')" :active="request()->routeIs('calm.*')" class="text-indigo-600">
                <i class="ri-leaf-line mr-1"></i> Zona Calma
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.show')">Ver Perfil</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">Definições</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            window.Echo.private('App.Models.User.{{ Auth::id() }}')
                .notification((notification) => {
                    window.dispatchEvent(new CustomEvent('new-notification'));
                });
        }
    });
</script>