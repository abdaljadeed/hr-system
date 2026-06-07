@php
    $field = 'mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<div>
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                  value="{{ old('title', $announcement->title ?? '') }}" required />
    <x-input-error :messages="$errors->get('title')" class="mt-1" />
</div>

<div>
    <x-input-label for="body" value="Message" />
    <textarea id="body" name="body" rows="4" class="{{ $field }}" required>{{ old('body', $announcement->body ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('body')" class="mt-1" />
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="{{ $field }}" required>
            @foreach(['info' => 'Info', 'warning' => 'Warning', 'success' => 'Success'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $announcement->type ?? 'info') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="expires_at" value="Expires At (optional)" />
        <x-text-input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 block w-full"
                      value="{{ old('expires_at', isset($announcement->expires_at) ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}" />
        <x-input-error :messages="$errors->get('expires_at')" class="mt-1" />
    </div>
</div>
