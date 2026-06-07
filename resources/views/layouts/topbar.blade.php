<header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button @click="$store.sidebar.open = !$store.sidebar.open" class="text-gray-500 hover:text-gray-700 lg:hidden">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="hidden min-w-0 lg:block">
            @yield('breadcrumb')
        </div>
    </div>

    <div class="flex items-center gap-4">
        @guest
            <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Log in</a>
        @endguest

        @auth
        @php
            $roleName = Auth::user()->getRoleNames()->first();
        @endphp

        @if ($roleName)
            <span class="hidden rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 sm:inline">
                {{ $roleName }}
            </span>
        @endif

        <x-dropdown align="right" width="w-80" contentClasses="bg-white">
            <x-slot name="trigger">
                <button class="relative inline-flex h-10 w-10 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <span class="text-sm font-semibold text-gray-800">Notifications</span>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Mark all read</button>
                        </form>
                    @endif
                </div>

                <div class="max-h-96 divide-y divide-gray-50 overflow-y-auto">
                    @forelse($recentNotifications as $notification)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                @include('notifications.partials.icon', ['type' => $notification->data['type'] ?? null])
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm text-gray-700">{{ $notification->data['message'] ?? 'Notification' }}</span>
                                    <span class="mt-0.5 block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                            </button>
                        </form>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-gray-400">You're all caught up.</p>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}"
                   class="block border-t border-gray-100 px-4 py-3 text-center text-sm font-medium text-indigo-600 hover:bg-gray-50">
                    View all notifications
                </a>
            </x-slot>
        </x-dropdown>

        <x-dropdown align="right" width="48">
            @php $topbarEmployee = Auth::user()->employee; @endphp
            <x-slot name="trigger">
                <button class="inline-flex items-center rounded-md border border-transparent bg-white px-2 py-1.5 text-sm font-medium leading-4 text-gray-600 transition hover:text-gray-900 focus:outline-none">
                    <span class="me-2 flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                        @if($topbarEmployee && $topbarEmployee->avatar_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($topbarEmployee->avatar_path) }}" class="h-full w-full object-cover" alt="">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </span>
                    <div>{{ Auth::user()->name }}</div>
                    <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('My Profile') }}
                </x-dropdown-link>

                @if(Auth::user()->employee)
                    <x-dropdown-link :href="route('employees.show', Auth::user()->employee)">
                        {{ __('My HR Profile') }}
                    </x-dropdown-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
        @endauth
    </div>
</header>
