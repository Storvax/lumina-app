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
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                <div class="relative mr-4" x-data="{ open: false, count: {{ Auth::user()->unreadNotifications->count() }} }" x-on:new-notification.window="count++">
                    <button @click="open = !open; if(open) { axios.post('{{ route('notifications.read') }}'); count = 0; }" 
                            class="relative p-2 text-slate-400 hover:text-slate-500 transition-colors focus:outline-none">
                        <i class="ri-notification-3-line text-xl"></i>
                        
                        <span x-show="count > 0" 
                              x-text="count"
                              class="absolute top-1 right-1 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] flex items-center justify-center border-2 border-white animate-pulse">
                        </span>
                    </button>

                    <div x-show="open" 
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden py-2"
                         style="display: none;">
                        
                        <div class="px-4 py-2 border-b border-slate-50 flex justify-between items-center">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Notificações</h3>
                            <button @click="open = false" class="text-slate-300 hover:text-slate-500"><i class="ri-close-line"></i></button>
                        </div>

                        <div class="max-h-64 overflow-y-auto">
                            @forelse(Auth::user()->notifications()->latest()->take(10)->get() as $notification)
                                @php $data = $notification->data; @endphp
                                <a href="{{ isset($data['post_id']) ? route('forum.show', $data['post_id']) : '#' }}" class="block px-4 py-3 hover:bg-slate-50 transition-colors {{ $notification->read_at ? 'opacity-60' : 'bg-indigo-50/30' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full {{ ($data['type'] ?? '') == 'reaction' ? 'bg-rose-100 text-rose-500' : 'bg-indigo-100 text-indigo-500' }} flex items-center justify-center text-sm shrink-0">
                                            <i class="{{ ($data['type'] ?? '') == 'reaction' ? 'ri-heart-fill' : 'ri-chat-1-fill' }}"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-slate-600 leading-tight">{{ $data['message'] ?? 'Nova notificação' }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center text-slate-400 text-sm">
                                    Nada de novo por agora. 🍃
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
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('forum.index')" :active="request()->routeIs('forum.*')">
                Mural
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
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
            // Escuta no canal privado do utilizador
            window.Echo.private('App.Models.User.{{ Auth::id() }}')
                .notification((notification) => {
                    // Dispara evento para o Alpine.js atualizar o contador
                    window.dispatchEvent(new CustomEvent('new-notification'));
                    
                    // Opcional: Efeito sonoro
                    // new Audio('/sounds/notification.mp3').play().catch(e => {}); 
                });
        }
    });
</script>