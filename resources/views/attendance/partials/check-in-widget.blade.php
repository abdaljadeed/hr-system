@php
    $employee   = auth()->user()->employee;
    $todayRecord = $employee
        ? \App\Models\Attendance::forEmployee($employee->id)
              ->forDate(now()->toDateString())
              ->first()
        : null;

    $checkedIn  = $todayRecord?->check_in  !== null;
    $checkedOut = $todayRecord?->check_out !== null;
@endphp

@if($employee)
<div class="rounded-lg bg-white p-6 shadow">
    <h3 class="mb-4 text-sm font-semibold text-gray-700">Today's Attendance</h3>

    <div class="mb-4 flex items-center gap-4">
        @if($checkedOut)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                Done for today
            </span>
            <span class="text-sm text-gray-500">
                {{ $todayRecord->check_in->format('H:i') }} → {{ $todayRecord->check_out->format('H:i') }}
                &nbsp;·&nbsp; {{ $todayRecord->worked_hours_formatted }}
            </span>
        @elseif($checkedIn)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                Checked in at {{ $todayRecord->check_in->format('H:i') }}
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600">
                Not checked in
            </span>
        @endif
    </div>

    <div class="flex gap-3">
        @if(! $checkedIn)
            <form method="POST" action="{{ route('attendance.check-in', $employee) }}">
                @csrf
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Check In
                </button>
            </form>
        @elseif(! $checkedOut)
            <form method="POST" action="{{ route('attendance.check-out', $employee) }}">
                @csrf
                <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Check Out
                </button>
            </form>
        @endif

        <a href="{{ route('attendance.show', $employee) }}"
           class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            My Records
        </a>
    </div>
</div>
@endif
