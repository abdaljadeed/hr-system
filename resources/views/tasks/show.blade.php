<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Tasks', 'href' => route('tasks.index')], ['label' => $task->title]]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="text-gray-400 hover:text-gray-600">Tasks</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">{{ $task->title }}</h2>
        </div>
    </x-slot>

    @php
        $statusBadge = match($task->status) {
            'todo'        => 'bg-gray-200 text-gray-700',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'submitted'   => 'bg-blue-100 text-blue-800',
            'approved'    => 'bg-green-100 text-green-800',
            'rejected'    => 'bg-red-100 text-red-800',
        };
        $priorityBadge = match($task->priority) {
            'low'    => 'bg-gray-100 text-gray-600',
            'medium' => 'bg-blue-100 text-blue-700',
            'high'   => 'bg-red-100 text-red-700',
        };
    @endphp

    @if($task->is_due_soon)
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><span class="font-semibold">Due soon —</span> this task is due {{ $task->due_date->format('d M Y') }}.</span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusBadge }}">{{ $task->status_label }}</span>
                    <span class="rounded-full px-3 py-1 text-sm font-medium {{ $priorityBadge }}">{{ ucfirst($task->priority) }} Priority</span>
                    @if($task->isOverdue())
                        <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">Overdue</span>
                    @endif
                </div>

                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Assigned To</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $task->assignee?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Assigned By</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $task->assigner?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Due Date</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $task->due_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>

                @if($task->description)
                    <div class="mt-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Description</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $task->description }}</dd>
                    </div>
                @endif

                @if($task->reviewed_at)
                    <div class="mt-4 rounded-md bg-gray-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Reviewed by {{ $task->reviewer?->name ?? '—' }} · {{ $task->reviewed_at->format('d M Y, H:i') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-700">{{ $task->review_notes ?: 'No notes provided.' }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex flex-wrap items-center gap-3">
                    @can('start', $task)
                        <form method="POST" action="{{ route('tasks.start', $task) }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600">
                                {{ $task->status === 'rejected' ? 'Resume Work' : 'Start Task' }}
                            </button>
                        </form>
                    @endcan

                    @can('submit', $task)
                        <form method="POST" action="{{ route('tasks.submit', $task) }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Submit for Review
                            </button>
                        </form>
                    @endcan

                    @can('update', $task)
                        <a href="{{ route('tasks.edit', $task) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                    @endcan

                    @can('delete', $task)
                        <span x-data>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" x-ref="deleteForm" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button" class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                    @click="$dispatch('confirm', { title: 'Delete Task', message: 'This task and its history will be removed.', onConfirm: () => $refs.deleteForm.submit() })">
                                Delete
                            </button>
                        </span>
                    @endcan
                </div>

                @can('review', $task)
                    <form method="POST" action="{{ route('tasks.approve', $task) }}" class="mt-4 space-y-3 border-t border-gray-100 pt-4">
                        @csrf
                        <x-input-label for="review_notes" value="Review Notes (optional)" />
                        <textarea id="review_notes" name="review_notes" rows="2"
                                  class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('review_notes') }}</textarea>
                        <div class="flex gap-3">
                            <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Approve</button>
                            <button type="submit" formaction="{{ route('tasks.reject', $task) }}"
                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Reject</button>
                        </div>
                    </form>
                @endcan

                @can('reassign', $task)
                    <form method="POST" action="{{ route('tasks.reassign', $task) }}" class="mt-4 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        <div>
                            <x-input-label for="assigned_to" value="Reassign To" />
                            <select id="assigned_to" name="assigned_to" required
                                    class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" @selected($assignee->id === $task->assigned_to)>{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reassign</button>
                    </form>
                @endcan
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Comments</h3>

                <div class="space-y-4">
                    @forelse($task->comments as $comment)
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                                {{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm">
                                    <span class="font-medium text-gray-900">{{ $comment->user?->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-gray-400">· {{ $comment->created_at->diffForHumans() }}</span>
                                </p>
                                <p class="mt-0.5 whitespace-pre-line text-sm text-gray-700">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No comments yet.</p>
                    @endforelse
                </div>

                @can('comment', $task)
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-4 space-y-3 border-t border-gray-100 pt-4">
                        @csrf
                        <textarea name="body" rows="2" required placeholder="Write a comment…"
                                  class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-1" />
                        <x-primary-button>Comment</x-primary-button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">History</h3>
            <ol class="relative space-y-5 border-l border-gray-200 pl-5">
                @foreach($task->histories->sortByDesc('created_at') as $history)
                    <li class="relative">
                        <span class="absolute -left-[1.65rem] top-1 h-3 w-3 rounded-full border-2 border-white bg-indigo-400"></span>
                        <p class="text-sm text-gray-800">
                            @if($history->action === 'created')
                                <span class="font-medium">{{ $history->user?->name }}</span> created the task
                            @elseif($history->action === 'reassigned')
                                <span class="font-medium">{{ $history->user?->name }}</span> reassigned the task
                            @else
                                <span class="font-medium">{{ $history->user?->name }}</span>
                                moved it to <span class="font-medium">{{ ucwords(str_replace('_', ' ', $history->to_status)) }}</span>
                            @endif
                        </p>
                        @if($history->note)
                            <p class="mt-0.5 text-xs text-gray-500">{{ $history->note }}</p>
                        @endif
                        <p class="mt-0.5 text-xs text-gray-400">{{ $history->created_at->format('d M Y, H:i') }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</x-app-layout>
