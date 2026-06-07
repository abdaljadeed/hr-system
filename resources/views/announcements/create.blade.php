<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Announcements', 'href' => route('announcements.index')], ['label' => 'New']]" />
    @endsection

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">New Announcement</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('announcements.store') }}" class="space-y-5">
            @csrf
            @include('announcements.partials.form', ['announcement' => null])
            <div class="flex justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <x-primary-button>Publish</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
