<div>
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                  value="{{ old('title', $task->title ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('title')" class="mt-1" />
</div>

<div>
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="4"
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $task->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-1" />
</div>

@if(! is_null($assignees))
    <div>
        <x-input-label for="assigned_to" value="Assign To" />
        <select id="assigned_to" name="assigned_to" required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— Select —</option>
            @foreach($assignees as $assignee)
                <option value="{{ $assignee->id }}" @selected(old('assigned_to') == $assignee->id)>
                    {{ $assignee->name }} ({{ $assignee->employee?->job_title ?? 'No title' }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('assigned_to')" class="mt-1" />
    </div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="priority" value="Priority" />
        <select id="priority" name="priority" required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach(['low', 'medium', 'high'] as $p)
                <option value="{{ $p }}" @selected(old('priority', $task->priority ?? 'medium') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('priority')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="due_date" value="Due Date" />
        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                      value="{{ old('due_date', $task?->due_date?->toDateString() ?? '') }}" />
        <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
    </div>
</div>
