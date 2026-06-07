<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Activity Log']]" />
    @endsection

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Activity Log</h2>
    </x-slot>

    @php
        $subjectUrl = function ($activity) {
            $subject = $activity->subject;
            if (! $subject) {
                return null;
            }

            return match ($activity->subject_type) {
                'App\Models\Employee'     => route('employees.show', $subject),
                'App\Models\LeaveRequest' => route('leaves.show', $subject),
                'App\Models\Payroll'      => route('payroll.show', $subject),
                'App\Models\Task'         => route('tasks.show', $subject),
                'App\Models\Attendance'   => route('attendance.show', $subject->employee_id),
                default                   => null,
            };
        };

        $subjectTitle = function ($activity) {
            $subject = $activity->subject;
            if (! $subject) {
                return null;
            }

            return match ($activity->subject_type) {
                'App\Models\Employee'     => $subject->full_name,
                'App\Models\LeaveRequest' => 'Request #'.$subject->id,
                'App\Models\Payroll'      => $subject->period_label,
                'App\Models\Task'         => $subject->title,
                'App\Models\Attendance'   => $subject->date->format('d M Y'),
                default                   => '#'.$subject->getKey(),
            };
        };
    @endphp

    <form method="GET" action="{{ route('activity.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-lg bg-white p-4 shadow">
        <div>
            <x-input-label for="causer_id" value="Performed By" />
            <select id="causer_id" name="causer_id"
                    class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Anyone</option>
                @foreach($causers as $causer)
                    <option value="{{ $causer->id }}" @selected(($filters['causer_id'] ?? '') == $causer->id)>{{ $causer->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="subject_type" value="Type" />
            <select id="subject_type" name="subject_type"
                    class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach($subjectTypes as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['subject_type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="from_date" value="From" />
            <x-text-input id="from_date" name="from_date" type="date" class="mt-1 block"
                          value="{{ $filters['from_date'] ?? '' }}" />
        </div>
        <div>
            <x-input-label for="to_date" value="To" />
            <x-text-input id="to_date" name="to_date" type="date" class="mt-1 block"
                          value="{{ $filters['to_date'] ?? '' }}" />
        </div>
        <x-primary-button>Filter</x-primary-button>
        @if(array_filter($filters))
            <a href="{{ route('activity.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3 text-left">Date / Time</th>
                    <th class="px-6 py-3 text-left">Action</th>
                    <th class="px-6 py-3 text-left">Performed By</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Subject</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($activities as $activity)
                    @php
                        $url = $subjectUrl($activity);
                        $title = $subjectTitle($activity);
                        $causerRole = $activity->causer?->getRoleNames()->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                            {{ $activity->created_at->format('d M Y') }}
                            <span class="block text-xs text-gray-400">{{ $activity->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-800">{{ $activity->description }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($activity->causer)
                                <span class="font-medium text-gray-900">{{ $activity->causer->name }}</span>
                                @if($causerRole)
                                    <span class="ml-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $causerRole }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                            {{ $subjectTypes[$activity->subject_type] ?? class_basename($activity->subject_type ?? '—') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($url)
                                <a href="{{ $url }}" class="text-indigo-600 hover:text-indigo-900">{{ $title }}</a>
                            @elseif($title)
                                <span class="text-gray-700">{{ $title }}</span>
                            @else
                                <span class="text-xs italic text-gray-400">deleted</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-0">
                            <x-empty-state
                                icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                heading="No activity recorded"
                                subtext="No audit entries match the selected filters."
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($activities->hasPages())
        <div class="mt-4">{{ $activities->links() }}</div>
    @endif
</x-app-layout>
