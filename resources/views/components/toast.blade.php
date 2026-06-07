@php
    $types = [
        'success' => ['bar' => 'bg-green-500', 'icon' => 'text-green-500', 'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'error' => ['bar' => 'bg-red-500', 'icon' => 'text-red-500', 'path' => 'M9.879 14.121L14.12 9.88m0 4.242L9.88 9.879M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'warning' => ['bar' => 'bg-yellow-500', 'icon' => 'text-yellow-500', 'path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        'info' => ['bar' => 'bg-blue-500', 'icon' => 'text-blue-500', 'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $active = collect($types)->filter(fn ($cfg, $key) => session()->has($key));
@endphp

@if($active->isNotEmpty())
    <div class="fixed bottom-4 right-4 z-[60] flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-3">
        @foreach($active as $key => $cfg)
            <div
                x-data="{ show: false, width: 100 }"
                x-init="$nextTick(() => { show = true; requestAnimationFrame(() => width = 0); setTimeout(() => show = false, 4000); })"
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-3 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/5"
                role="alert"
            >
                <div class="flex items-start gap-3 p-4">
                    <svg class="h-5 w-5 shrink-0 {{ $cfg['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['path'] }}" />
                    </svg>
                    <p class="flex-1 text-sm font-medium text-gray-700">{{ session($key) }}</p>
                    <button type="button" @click="show = false" class="text-gray-400 transition hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 {{ $cfg['bar'] }}" :style="`width: ${width}%; transition: width 4000ms linear`"></div>
            </div>
        @endforeach
    </div>
@endif
