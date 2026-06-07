<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Server Error</h2>
    </x-slot>

    <div class="rounded-lg bg-white shadow">
        <x-empty-state
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            heading="500 — Something Went Wrong"
            subtext="An unexpected error occurred on our end. Please try again in a moment, or contact your administrator if the problem persists."
            actionLabel="Back to Dashboard"
            :actionHref="route('dashboard')"
        />
    </div>
</x-app-layout>
