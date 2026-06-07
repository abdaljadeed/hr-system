<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.show', $task) }}" class="text-gray-400 hover:text-gray-600">Task</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">Edit Task</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-5 rounded-lg bg-white p-6 shadow">
            @csrf
            @method('PUT')
            @include('tasks.partials.form', ['task' => $task, 'assignees' => null])

            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('tasks.show', $task) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
