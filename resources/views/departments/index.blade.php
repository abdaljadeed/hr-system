<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Departments']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Departments</h2>
            @can('departments.manage')
                <a href="{{ route('departments.create') }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    + New Department
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 px-4 py-4">
            <form method="GET" class="flex items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search departments…"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('departments.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Code</th>
                    <th class="px-6 py-3 text-left">Manager</th>
                    <th class="px-6 py-3 text-left">Employees</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($departments as $department)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $department->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $department->code }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $department->manager?->full_name ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $department->employees_count }}</td>
                        <td class="px-6 py-4 text-right">
                            @can('departments.manage')
                                <a href="{{ route('departments.edit', $department) }}"
                                   class="text-gray-600 hover:text-gray-900">Edit</a>
                                <span x-data class="ml-3 inline">
                                    <form method="POST" action="{{ route('departments.destroy', $department) }}" x-ref="deleteForm" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button" class="text-red-600 hover:text-red-900"
                                            @click="$dispatch('confirm', { title: 'Delete Department', message: 'This action cannot be undone.', onConfirm: () => $refs.deleteForm.submit() })">
                                        Delete
                                    </button>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-0">
                            <x-empty-state
                                icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                heading="No departments found"
                                subtext="Create a department to organize your employees."
                                :actionLabel="auth()->user()->can('departments.manage') ? 'New Department' : null"
                                :actionHref="auth()->user()->can('departments.manage') ? route('departments.create') : null"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($departments->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
