<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('departments.index') }}" class="text-gray-400 hover:text-gray-600">Departments</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">Edit: {{ $department->name }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('departments.update', $department) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PATCH')

        <div class="rounded-lg bg-white p-6 shadow space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Department Name *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                  value="{{ old('name', $department->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="code" value="Code *" />
                    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                  value="{{ old('code', $department->code) }}" required />
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="manager_id" value="Manager" />
                    <select id="manager_id" name="manager_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">— No manager —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(old('manager_id', $department->manager_id) == $emp->id)>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('manager_id')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $department->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Update Department</x-primary-button>
            <a href="{{ route('departments.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</x-app-layout>
