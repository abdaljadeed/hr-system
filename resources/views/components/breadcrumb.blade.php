@props(['items' => []])

<nav class="flex items-center gap-1.5 truncate text-sm text-gray-400">
    <a href="{{ route('dashboard') }}" class="shrink-0 hover:text-gray-600">Dashboard</a>
    @foreach($items as $item)
        <span class="shrink-0 text-gray-300">/</span>
        @if(! empty($item['href']) && ! $loop->last)
            <a href="{{ $item['href'] }}" class="shrink-0 hover:text-gray-600">{{ $item['label'] }}</a>
        @else
            <span class="truncate font-medium text-gray-700">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
