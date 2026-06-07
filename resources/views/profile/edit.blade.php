<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'My Profile']]" />
    @endsection

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">My Profile</h2>
    </x-slot>

    <div class="max-w-2xl" x-data="{ tab: 'personal' }">
        <div class="mb-6 flex gap-2 border-b border-gray-200">
            <button type="button" @click="tab = 'personal'"
                    :class="tab === 'personal' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="border-b-2 px-4 py-2 text-sm font-medium transition">
                Personal Info
            </button>
            <button type="button" @click="tab = 'security'"
                    :class="tab === 'security' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="border-b-2 px-4 py-2 text-sm font-medium transition">
                Security
            </button>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="rounded-lg bg-white p-6 shadow">
            @csrf
            @method('PATCH')

            <div x-show="tab === 'personal'" class="space-y-5">
                @if($employee)
                    <div class="flex items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 text-lg font-semibold text-indigo-700">
                            @if($employee->avatar_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($employee->avatar_path) }}" alt="Avatar" class="h-full w-full object-cover">
                            @else
                                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                            @endif
                        </span>
                        <div>
                            <x-input-label for="avatar" value="Profile Photo" />
                            <input id="avatar" name="avatar" type="file" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                            <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="phone" value="Phone Number" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                      value="{{ old('phone', $employee->phone) }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="address" value="Address" />
                        <textarea id="address" name="address" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $employee->address) }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>
                @else
                    <p class="text-sm text-gray-500">Your account isn't linked to an employee record, so there's no personal info to edit. You can still update your password under Security.</p>
                @endif
            </div>

            <div x-show="tab === 'security'" x-cloak class="space-y-5">
                <div>
                    <x-input-label for="current_password" value="Current Password" />
                    <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password" value="New Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirm New Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                </div>
                <p class="text-xs text-gray-400">Leave password fields blank to keep your current password.</p>
            </div>

            <div class="mt-6 flex justify-end">
                <x-primary-button>Save Changes</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
