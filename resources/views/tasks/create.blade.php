<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="text-gray-400 hover:text-gray-600">Tasks</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">New Task</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('tasks.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow">
            @csrf
            @include('tasks.partials.form', ['task' => null, 'assignees' => $assignees])

            <div class="flex items-center gap-3">
                <x-primary-button>Create Task</x-primary-button>
                <a href="{{ route('tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
