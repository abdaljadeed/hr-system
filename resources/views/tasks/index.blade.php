<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Tasks']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Tasks</h2>
            @can('create', \App\Models\Task::class)
                <a href="{{ route('tasks.create') }}"
                   class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    New Task
                </a>
            @endcan
        </div>
    </x-slot>

    <form method="GET" action="{{ route('tasks.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <x-input-label for="search" value="Search" />
            <x-text-input id="search" name="search" type="text" class="mt-1 block w-56"
                          value="{{ $filters['search'] ?? '' }}" placeholder="Title…" />
        </div>
        <div>
            <x-input-label for="priority" value="Priority" />
            <select id="priority" name="priority"
                    class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach(['low', 'medium', 'high'] as $p)
                    <option value="{{ $p }}" @selected(($filters['priority'] ?? '') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <x-primary-button>Filter</x-primary-button>
        @if(array_filter($filters))
            <a href="{{ route('tasks.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif
    </form>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach($statuses as $status)
            @php
                $headBadge = match($status) {
                    'todo'        => 'bg-gray-200 text-gray-700',
                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                    'submitted'   => 'bg-blue-100 text-blue-800',
                    'approved'    => 'bg-green-100 text-green-800',
                    'rejected'    => 'bg-red-100 text-red-800',
                };
            @endphp
            <div class="rounded-lg bg-gray-50 p-3">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $headBadge }}">{{ $columns[$status]->count() }}</span>
                </div>

                <div class="space-y-3">
                    @forelse($columns[$status] as $task)
                        @php
                            $priorityBadge = match($task->priority) {
                                'low'    => 'bg-gray-100 text-gray-600',
                                'medium' => 'bg-blue-100 text-blue-700',
                                'high'   => 'bg-red-100 text-red-700',
                            };
                        @endphp
                        <a href="{{ route('tasks.show', $task) }}"
                           class="block rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-100 transition hover:shadow">
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $priorityBadge }}">{{ ucfirst($task->priority) }}</span>
                            </div>
                            @if($task->is_due_soon)
                                <span class="mb-2 inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Due Soon
                                </span>
                            @endif
                            <p class="text-xs text-gray-500">{{ $task->assignee?->name ?? '—' }}</p>
                            @if($task->due_date)
                                <p class="mt-1 text-xs {{ $task->isOverdue() ? 'font-medium text-red-600' : 'text-gray-400' }}">
                                    Due {{ $task->due_date->format('d M Y') }}
                                </p>
                            @endif
                        </a>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-200 py-6 text-center text-xs text-gray-400">No tasks</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
