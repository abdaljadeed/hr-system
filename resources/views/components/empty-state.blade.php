@props([
    'icon' => null,
    'heading' => 'Nothing here yet',
    'subtext' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

@php
    $defaultIcon = 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4';
@endphp

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon ?? $defaultIcon }}" />
    </svg>
    <h3 class="mt-4 text-sm font-semibold text-gray-700">{{ $heading }}</h3>
    @if($subtext)
        <p class="mt-1 max-w-sm text-sm text-gray-400">{{ $subtext }}</p>
    @endif
    @if($actionLabel && $actionHref)
        <a href="{{ $actionHref }}"
           class="mt-5 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            {{ $actionLabel }}
        </a>
    @endif
</div>
