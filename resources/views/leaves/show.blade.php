<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Leave Management', 'href' => route('leaves.index')], ['label' => 'Request #' . $leave->id]]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('leaves.index') }}" class="text-gray-400 hover:text-gray-600">Leave Management</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">Leave Request #{{ $leave->id }}</h2>
        </div>
    </x-slot>

    @php
        $badge = match($leave->status) {
            'pending'   => 'bg-yellow-100 text-yellow-800',
            'approved'  => 'bg-green-100 text-green-800',
            'rejected'  => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="max-w-3xl space-y-6">
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $badge }}">{{ ucfirst($leave->status) }}</span>
                <span class="text-sm text-gray-400">Requested {{ $leave->created_at->format('d M Y, H:i') }}</span>
            </div>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Employee</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $leave->employee->full_name }}
                        <span class="text-gray-400">· {{ $leave->employee->department?->name ?? '—' }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Leave Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $leave->leaveType->name }}
                        <span class="text-gray-400">· {{ $leave->leaveType->is_paid ? 'Paid' : 'Unpaid' }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Period</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Working Days</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ rtrim(rtrim(number_format($leave->days, 1), '0'), '.') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Reason</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $leave->reason ?: '—' }}</dd>
                </div>
                @if($leave->reviewed_at)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Reviewed By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $leave->reviewer?->name ?? '—' }}
                            <span class="text-gray-400">· {{ $leave->reviewed_at->format('d M Y, H:i') }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Review Notes</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $leave->review_notes ?: '—' }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        @can('approve', $leave)
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-base font-semibold text-gray-900">Review</h3>
                <form method="POST" action="{{ route('leaves.approve', $leave) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="review_notes" value="Notes (optional)" />
                        <textarea id="review_notes" name="review_notes" rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('review_notes') }}</textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            Approve
                        </button>
                        <button type="submit" formaction="{{ route('leaves.reject', $leave) }}"
                                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        @endcan

        @can('cancel', $leave)
            <form method="POST" action="{{ route('leaves.cancel', $leave) }}"
                  onsubmit="return confirm('Cancel this leave request?');">
                @csrf
                <button type="submit"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel Request
                </button>
            </form>
        @endcan
    </div>
</x-app-layout>
