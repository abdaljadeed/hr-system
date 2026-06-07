@if($balances->isNotEmpty())
    <div class="mb-6">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">My Leave Balances ({{ now()->year }})</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach($balances as $balance)
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $balance->leaveType->name }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ rtrim(rtrim(number_format($balance->remaining_days, 1), '0'), '.') }}</p>
                    <p class="text-xs text-gray-400">
                        of {{ rtrim(rtrim(number_format($balance->entitled_days, 1), '0'), '.') }} days left
                    </p>
                </div>
            @endforeach
        </div>
    </div>
@endif
