<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Access Denied</h2>
    </x-slot>

    <div class="rounded-lg bg-white shadow">
        <x-empty-state
            icon="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
            heading="403 — Access Denied"
            subtext="You don't have permission to view this page. If you believe this is a mistake, contact your administrator."
            actionLabel="Back to Dashboard"
            :actionHref="route('dashboard')"
        />
    </div>
</x-app-layout>
