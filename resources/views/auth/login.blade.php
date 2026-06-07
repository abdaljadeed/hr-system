<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name', 'HR System') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen">
        <div class="hidden w-2/5 flex-col justify-between bg-gray-900 p-12 text-white lg:flex">
            <div class="flex items-center gap-3">
                <x-application-logo class="h-9 w-9 fill-current text-white" />
                <span class="text-xl font-semibold">{{ config('app.name', 'HR System') }}</span>
            </div>

            <div class="space-y-8">
                <div>
                    <h1 class="text-3xl font-bold leading-tight">Everything your<br>HR team needs.</h1>
                    <p class="mt-3 text-sm text-gray-400">One platform for people, attendance, leave, and payroll.</p>
                </div>

                <ul class="space-y-5">
                    @php
                        $features = [
                            ['Manage your workforce', 'Employees, departments, roles & profiles in one place.', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
                            ['Track attendance & leaves', 'Check-in/out, approvals, and balances that stay in sync.', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['Payroll in one click', 'Generate finalized payslips with bonuses & deductions.', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m9-5a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];
                    @endphp
                    @foreach($features as $feature)
                        <li class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature[2] }}" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold">{{ $feature[0] }}</p>
                                <p class="text-sm text-gray-400">{{ $feature[1] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} {{ config('app.name', 'HR System') }}. All rights reserved.</p>
        </div>

        <div class="flex w-full items-center justify-center bg-gray-100 px-6 py-12 lg:w-3/5">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-2 lg:hidden">
                    <x-application-logo class="h-8 w-8 fill-current text-gray-800" />
                    <span class="text-lg font-semibold text-gray-800">{{ config('app.name', 'HR System') }}</span>
                </div>

                <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                <p class="mt-1 text-sm text-gray-500">Sign in to your account to continue.</p>

                <x-auth-session-status class="mt-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-indigo-600 hover:text-indigo-800" href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('Sign In') }}
                    </button>
                </form>

              
            </div>
        </div>
    </div>
</body>
</html>
