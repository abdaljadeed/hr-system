<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Page Not Found</h2>
    </x-slot>

    <div class="rounded-lg bg-white shadow">
        <x-empty-state
            icon="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            heading="404 — Page Not Found"
            subtext="The page you're looking for doesn't exist or may have been moved."
            actionLabel="Back to Dashboard"
            :actionHref="route('dashboard')"
        />
    </div>
</x-app-layout>
