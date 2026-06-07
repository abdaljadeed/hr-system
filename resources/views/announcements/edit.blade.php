<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Announcements', 'href' => route('announcements.index')], ['label' => 'Edit']]" />
    @endsection

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Announcement</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('announcements.partials.form', ['announcement' => $announcement])

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       @checked(old('is_active', $announcement->is_active))>
                Active
            </label>

            <div class="flex justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <x-primary-button>Save Changes</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
