<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('leaves.index') }}" class="text-gray-400 hover:text-gray-600">Leave Management</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold text-gray-800">Request Leave</h2>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @include('leaves.partials.balances')

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('leaves.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <x-input-label for="leave_type_id" value="Leave Type *" />
                <select id="leave_type_id" name="leave_type_id"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">— Select type —</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                            {{ $type->name }} ({{ $type->is_paid ? 'Paid' : 'Unpaid' }})
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('leave_type_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="start_date" value="Start Date *" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full"
                              value="{{ old('start_date', now()->toDateString()) }}" required />
                <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="end_date" value="End Date *" />
                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full"
                              value="{{ old('end_date', now()->toDateString()) }}" required />
                <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="reason" value="Reason" />
                <textarea id="reason" name="reason" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('reason') }}</textarea>
                <x-input-error :messages="$errors->get('reason')" class="mt-1" />
            </div>

            <div class="sm:col-span-2 flex items-center gap-3">
                <x-primary-button>Submit Request</x-primary-button>
                <a href="{{ route('leaves.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
        <p class="mt-4 text-xs text-gray-400">Weekends are excluded automatically when counting leave days.</p>
    </div>
</x-app-layout>
