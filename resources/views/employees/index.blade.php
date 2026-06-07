<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Employees']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Employees</h2>
            @can('employees.create')
                <a href="{{ route('employees.create') }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    + New Employee
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-lg bg-white shadow">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-4">
            <form method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, code, title…"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />

                <select name="department_id"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="active"     @selected(request('status') === 'active')>Active</option>
                    <option value="probation"  @selected(request('status') === 'probation')>Probation</option>
                    <option value="terminated" @selected(request('status') === 'terminated')>Terminated</option>
                </select>

                <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Filter
                </button>

                @if(request()->hasAny(['search','department_id','status']))
                    <a href="{{ route('employees.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </form>

            @hasanyrole('Admin|HR Manager')
                <form method="POST" action="{{ route('reports.excel') }}">
                    @csrf
                    <input type="hidden" name="report_type" value="employees">
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
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
                    <th class="px-6 py-3 text-left">Employee</th>
                    <th class="px-6 py-3 text-left">Code</th>
                    <th class="px-6 py-3 text-left">Department</th>
                    <th class="px-6 py-3 text-left">Job Title</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Hired</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $employee->full_name }}
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $employee->employee_code }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $employee->department?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $employee->job_title }}</td>
                        <td class="px-6 py-4">
                            @php
                                $badge = match($employee->employment_status) {
                                    'active'     => 'bg-green-100 text-green-800',
                                    'probation'  => 'bg-yellow-100 text-yellow-800',
                                    'terminated' => 'bg-red-100 text-red-800',
                                };
                            @endphp
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ ucfirst($employee->employment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $employee->hire_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('employees.show', $employee) }}"
                               class="text-indigo-600 hover:text-indigo-900">View</a>
                            @can('employees.update')
                                <a href="{{ route('employees.edit', $employee) }}"
                                   class="ml-3 text-gray-600 hover:text-gray-900">Edit</a>
                            @endcan
                            @can('employees.delete')
                                <span x-data class="ml-3 inline">
                                    <form method="POST" action="{{ route('employees.destroy', $employee) }}" x-ref="deleteForm" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button" class="text-red-600 hover:text-red-900"
                                            @click="$dispatch('confirm', { title: 'Delete Employee', message: 'This action cannot be undone.', onConfirm: () => $refs.deleteForm.submit() })">
                                        Delete
                                    </button>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-0">
                            <x-empty-state
                                icon="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"
                                heading="No employees found"
                                subtext="Try adjusting your filters, or add the first employee to get started."
                                :actionLabel="auth()->user()->can('employees.create') ? 'New Employee' : null"
                                :actionHref="auth()->user()->can('employees.create') ? route('employees.create') : null"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($employees->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
