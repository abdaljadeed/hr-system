<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Notifications']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Notifications</h2>
            @if(auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="divide-y divide-gray-100">
            @forelse($notifications as $notification)
                @php $isUnread = is_null($notification->read_at); @endphp
                <div class="flex items-start gap-3 px-5 py-4 {{ $isUnread ? 'bg-indigo-50/40' : '' }}">
                    @include('notifications.partials.icon', ['type' => $notification->data['type'] ?? null])

                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="min-w-0 flex-1">
                        @csrf
                        <button type="submit" class="block w-full text-left">
                            <span class="block text-sm text-gray-800">{{ $notification->data['message'] ?? 'Notification' }}</span>
                            <span class="mt-0.5 block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </button>
                    </form>

                    <div class="flex shrink-0 items-center gap-3">
                        @if($isUnread)
                            <span class="h-2.5 w-2.5 rounded-full bg-indigo-500" title="Unread"></span>
                        @endif
                        <span x-data>
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" x-ref="deleteForm" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button" class="text-xs text-gray-400 hover:text-red-600"
                                    @click="$dispatch('confirm', { title: 'Delete Notification', message: 'This notification will be removed.', onConfirm: () => $refs.deleteForm.submit() })">
                                Delete
                            </button>
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-400">No notifications yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</x-app-layout>
