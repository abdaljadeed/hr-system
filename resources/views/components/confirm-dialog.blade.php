<div
    x-data="{ open: false, title: '', message: '', onConfirm: null }"
    x-cloak
    @confirm.window="title = $event.detail.title || 'Are you sure?'; message = $event.detail.message || ''; onConfirm = $event.detail.onConfirm || null; open = true"
    @keydown.escape.window="open = false"
    x-show="open"
    class="fixed inset-0 z-[70] flex items-center justify-center p-4"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-gray-900/50"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl"
    >
        <div class="flex items-start gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900" x-text="title"></h3>
                <p class="mt-1 text-sm text-gray-500" x-text="message"></p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="open = false"
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" @click="open = false; if (onConfirm) onConfirm();"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Confirm
            </button>
        </div>
    </div>
</div>
