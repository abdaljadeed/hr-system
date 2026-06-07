<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'employee' => $request->user()->employee,
        ]);
    }

    public function update(StoreProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $employee = $user->employee;

        if ($employee) {
            $fields = [];

            if (array_key_exists('phone', $data)) {
                $fields['phone'] = $data['phone'];
            }

            if (array_key_exists('address', $data)) {
                $fields['address'] = $data['address'];
            }

            if ($request->hasFile('avatar')) {
                if ($employee->avatar_path) {
                    Storage::disk('public')->delete($employee->avatar_path);
                }
                $fields['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
            }

            if ($fields) {
                $employee->update($fields);
            }
        }

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
