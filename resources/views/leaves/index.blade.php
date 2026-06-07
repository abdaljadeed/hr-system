<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Leave Management']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Leave Management</h2>
            @can('create', \App\Models\LeaveRequest::class)
                <a href="{{ route('leaves.create') }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    + Request Leave
                </a>
            @endcan
        </div>
    </x-slot>

    @include('leaves.partials.balances')

    <div class="rounded-lg bg-white shadow">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-4">
            <form method="GET" class="flex flex-wrap gap-3">
                <select name="status"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'approved', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <select name="leave_type_id"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>

                @can('leaves.approve')
                    <select name="department_id"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                @endcan

                <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Filter
                </button>
                @if(request()->hasAny(['status', 'leave_type_id', 'department_id']))
                    <a href="{{ route('leaves.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </form>

            @hasanyrole('Admin|HR Manager')
                <form method="POST" action="{{ route('reports.excel') }}">
                    @csrf
                    <input type="hidden" name="report_type" value="leaves">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="leave_type_id" value="{{ request('leave_type_id') }}">
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
                    @can('leaves.approve')
                        <th class="px-6 py-3 text-left">Employee</th>
                    @endcan
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Dates</th>
                    <th class="px-6 py-3 text-left">Days</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Requested</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($leaveRequests as $leave)
                    @php
                        $badge = match($leave->status) {
                            'pending'   => 'bg-yellow-100 text-yellow-800',
                            'approved'  => 'bg-green-100 text-green-800',
                            'rejected'  => 'bg-red-100 text-red-800',
                            'cancelled' => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        @can('leaves.approve')
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $leave->employee->full_name }}</td>
                        @endcan
                        <td class="px-6 py-4 text-gray-700">{{ $leave->leaveType->name }}</td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ rtrim(rtrim(number_format($leave->days, 1), '0'), '.') }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $leave->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('leaves.show', $leave) }}"
                               class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-0">
                            <x-empty-state
                                icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                heading="No leave requests found"
                                subtext="Leave requests will appear here once submitted."
                                :actionLabel="auth()->user()->can('create', \App\Models\LeaveRequest::class) ? 'Request Leave' : null"
                                :actionHref="auth()->user()->can('create', \App\Models\LeaveRequest::class) ? route('leaves.create') : null"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($leaveRequests->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
