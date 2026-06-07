@php
    $map = [
        'task.assigned'     => ['M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'bg-blue-50 text-blue-600'],
        'task.submitted'    => ['M5 10l7-7m0 0l7 7m-7-7v18', 'bg-indigo-50 text-indigo-600'],
        'task.reviewed'     => ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg-green-50 text-green-600'],
        'leave.submitted'   => ['M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'bg-amber-50 text-amber-600'],
        'leave.reviewed'    => ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'bg-emerald-50 text-emerald-600'],
        'payroll.finalized' => ['M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'bg-purple-50 text-purple-600'],
    ];
    [$path, $accent] = $map[$type] ?? ['M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'bg-gray-100 text-gray-500'];
@endphp

<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $accent }}">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
    </svg>
</span>
