<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Attendance']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Attendance</h2>
            @can('attendance.manage')
                <a href="#manual-entry"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    + Manual Entry
                </a>
            @endcan
        </div>
    </x-slot>

    @if(auth()->user()->employee)
        <div class="mb-6">
            @include('attendance.partials.check-in-widget')
        </div>
    @endif

    <div class="rounded-lg bg-white shadow">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-4">
            <form method="GET" class="flex flex-wrap gap-3">
                @can('attendance.manage')
                    <select name="employee_id"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="department_id"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                @endcan

                <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />

                <select name="status"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="present"  @selected(request('status') === 'present')>Present</option>
                    <option value="late"     @selected(request('status') === 'late')>Late</option>
                    <option value="absent"   @selected(request('status') === 'absent')>Absent</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                </select>

                <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Filter
                </button>
                @if(request()->hasAny(['employee_id','department_id','status']) || request('month') !== now()->format('Y-m'))
                    <a href="{{ route('attendance.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </form>

            @hasanyrole('Admin|HR Manager')
                @php [$expY, $expM] = array_pad(explode('-', request('month', now()->format('Y-m'))), 2, ''); @endphp
                <form method="POST" action="{{ route('reports.excel') }}">
                    @csrf
                    <input type="hidden" name="report_type" value="attendance">
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <input type="hidden" name="year" value="{{ $expY }}">
                    <input type="hidden" name="month" value="{{ (int) $expM }}">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Export Excel
                    </button>
                </form>
            @endhasanyrole
        </div>

        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3 text-left">Date</th>
                    @can('attendance.manage')
                        <th class="px-6 py-3 text-left">Employee</th>
                        <th class="px-6 py-3 text-left">Department</th>
                    @endcan
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Check In</th>
                    <th class="px-6 py-3 text-left">Check Out</th>
                    <th class="px-6 py-3 text-left">Worked</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($attendances as $record)
                    @php
                        $badge = match($record->status) {
                            'present'  => 'bg-green-100 text-green-800',
                            'late'     => 'bg-yellow-100 text-yellow-800',
                            'absent'   => 'bg-red-100 text-red-800',
                            'on_leave' => 'bg-blue-100 text-blue-800',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900">{{ $record->date->format('d M Y') }}</td>
                        @can('attendance.manage')
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="{{ route('attendance.show', $record->employee) }}"
                                   class="hover:text-indigo-600">
                                    {{ $record->employee->full_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $record->employee->department?->name ?? '—' }}</td>
                        @endcan
                        <td class="px-6 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $record->check_in?->format('H:i') ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $record->check_out?->format('H:i') ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $record->worked_hours_formatted }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('attendance.show', $record->employee) }}"
                               class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0">
                            <x-empty-state
                                icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                heading="No attendance records found"
                                subtext="No records match the selected filters for this period."
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($attendances->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

    @can('attendance.manage')
    <div id="manual-entry" class="mt-6 rounded-lg bg-white p-6 shadow">
        <h3 class="mb-4 text-base font-semibold text-gray-900">Manual Attendance Entry</h3>
        <form method="POST" action="{{ route('attendance.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @csrf
            <div>
                <x-input-label for="me_employee_id" value="Employee *" />
                <select id="me_employee_id" name="employee_id"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">— Select employee —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('employee_id')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="me_date" value="Date *" />
                <x-text-input id="me_date" name="date" type="date" class="mt-1 block w-full"
                              value="{{ old('date', now()->toDateString()) }}" required />
                <x-input-error :messages="$errors->get('date')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="me_status" value="Status *" />
                <select id="me_status" name="status"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="present"  @selected(old('status') === 'present')>Present</option>
                    <option value="late"     @selected(old('status') === 'late')>Late</option>
                    <option value="absent"   @selected(old('status') === 'absent')>Absent</option>
                    <option value="on_leave" @selected(old('status') === 'on_leave')>On Leave</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="me_check_in" value="Check In" />
                <x-text-input id="me_check_in" name="check_in" type="datetime-local" class="mt-1 block w-full"
                              value="{{ old('check_in') }}" />
                <x-input-error :messages="$errors->get('check_in')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="me_check_out" value="Check Out" />
                <x-text-input id="me_check_out" name="check_out" type="datetime-local" class="mt-1 block w-full"
                              value="{{ old('check_out') }}" />
                <x-input-error :messages="$errors->get('check_out')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="me_notes" value="Notes" />
                <x-text-input id="me_notes" name="notes" type="text" class="mt-1 block w-full"
                              value="{{ old('notes') }}" />
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>
            <div class="sm:col-span-3">
                <x-primary-button>Save Entry</x-primary-button>
            </div>
        </form>
    </div>
    @endcan
</x-app-layout>
