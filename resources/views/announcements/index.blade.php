<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Announcements']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Announcements</h2>
            <a href="{{ route('announcements.create') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                + New Announcement
            </a>
        </div>
    </x-slot>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3 text-left">Title</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Expires</th>
                    <th class="px-6 py-3 text-left">Published By</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($announcements as $announcement)
                    @php
                        $typeBadge = match($announcement->type) {
                            'warning' => 'bg-yellow-100 text-yellow-800',
                            'success' => 'bg-green-100 text-green-800',
                            default   => 'bg-blue-100 text-blue-800',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $announcement->title }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeBadge }}">{{ ucfirst($announcement->type) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($announcement->is_active && (! $announcement->expires_at || $announcement->expires_at->isFuture()))
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $announcement->expires_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $announcement->publisher?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('announcements.edit', $announcement) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                            @if($announcement->is_active)
                                <span x-data class="ml-3 inline">
                                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" x-ref="deleteForm" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button" class="text-red-600 hover:text-red-900"
                                            @click="$dispatch('confirm', { title: 'Deactivate Announcement', message: 'It will no longer appear on dashboards.', onConfirm: () => $refs.deleteForm.submit() })">
                                        Deactivate
                                    </button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0">
                            <x-empty-state
                                icon="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
                                heading="No announcements yet"
                                subtext="Publish an announcement to broadcast it on everyone's dashboard."
                                actionLabel="New Announcement"
                                :actionHref="route('announcements.create')"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($announcements->hasPages())
        <div class="mt-4">{{ $announcements->links() }}</div>
    @endif
</x-app-layout>
