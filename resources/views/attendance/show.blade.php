<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('attendance.index') }}" class="text-gray-400 hover:text-gray-600">Attendance</a>
                <span class="text-gray-300">/</span>
                <h2 class="text-xl font-semibold text-gray-800">{{ $employee->full_name }}</h2>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @if(auth()->user()->employee?->id === $employee->id)
        <div class="mb-6">
            @include('attendance.partials.check-in-widget')
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            @php
                $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
                $nextMonth = \Carbon\Carbon::create($year, $month, 1)->addMonth();
            @endphp
            <a href="?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                &larr;
            </a>
            <span class="min-w-32 text-center text-sm font-semibold text-gray-800">
                {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </span>
            <a href="?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                &rarr;
            </a>
        </form>
    </div>

    @php
        $summary = $report['summary'];
        $cards = [
            ['Present',  $summary['present'],      'bg-green-50 text-green-800'],
            ['Late',     $summary['late'],          'bg-yellow-50 text-yellow-800'],
            ['Absent',   $summary['absent'],        'bg-red-50 text-red-800'],
            ['On Leave', $summary['on_leave'],      'bg-blue-50 text-blue-800'],
            ['Worked',   number_format($summary['total_worked'], 1).'h', 'bg-indigo-50 text-indigo-800'],
        ];
    @endphp
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        @foreach($cards as [$label, $value, $cls])
            <div class="rounded-lg {{ $cls }} p-4 text-center">
                <p class="text-2xl font-bold">{{ $value }}</p>
                <p class="text-xs font-medium uppercase tracking-wide">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg bg-white shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Day</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Check In</th>
                    <th class="px-6 py-3 text-left">Check Out</th>
                    <th class="px-6 py-3 text-left">Worked</th>
                    <th class="px-6 py-3 text-left">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @php
                    $recordsByDate = $report['records']->keyBy(fn($r) => $r->date->format('Y-m-d'));
                    $daysInMonth   = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                @endphp
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date   = \Carbon\Carbon::create($year, $month, $day);
                        $key    = $date->format('Y-m-d');
                        $record = $recordsByDate[$key] ?? null;
                        $isWeekend = $date->isWeekend();
                        $badge  = $record ? match($record->status) {
                            'present'  => 'bg-green-100 text-green-800',
                            'late'     => 'bg-yellow-100 text-yellow-800',
                            'absent'   => 'bg-red-100 text-red-800',
                            'on_leave' => 'bg-blue-100 text-blue-800',
                        } : '';
                    @endphp
                    <tr class="{{ $isWeekend ? 'bg-gray-50' : 'hover:bg-gray-50' }}">
                        <td class="px-6 py-3 font-medium {{ $date->isToday() ? 'text-indigo-600' : 'text-gray-900' }}">
                            {{ $date->format('d M') }}
                        </td>
                        <td class="px-6 py-3 text-gray-400 text-xs">{{ $date->format('D') }}</td>
                        <td class="px-6 py-3">
                            @if($record)
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            @elseif($isWeekend)
                                <span class="text-xs text-gray-400">Weekend</span>
                            @elseif($date->isFuture())
                                <span class="text-xs text-gray-300">—</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">No record</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $record?->check_in?->format('H:i') ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $record?->check_out?->format('H:i') ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $record?->worked_hours_formatted ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-400 text-xs">{{ $record?->notes ?? '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</x-app-layout>
