<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HR System') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>[x-cloak]{ display: none !important; }</style>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', { open: window.innerWidth >= 1024 });
            });
            window.addEventListener('resize', () => {
                if (window.Alpine && Alpine.store('sidebar')) {
                    Alpine.store('sidebar').open = window.innerWidth >= 1024;
                }
            });
        </script>
    </head>
    <body class="font-sans antialiased">
        <div x-data class="min-h-screen bg-gray-100">
            <div
                x-cloak
                x-show="$store.sidebar.open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="$store.sidebar.open = false"
                class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden"
            ></div>

            @include('layouts.sidebar')

            <div class="lg:pl-64 transition-[padding] duration-200">
                @include('layouts.topbar')

                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>

            <x-toast />
            <x-confirm-dialog />
        </div>

        @stack('scripts')

        <script>
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;

                const submitter = event.submitter;
                const method = (submitter && submitter.formMethod ? submitter.formMethod : form.method).toLowerCase();
                if (method === 'get' && submitter && submitter.formTarget === '_blank') return;

                const buttons = form.querySelectorAll('button[type="submit"]');
                buttons.forEach(function (button) {
                    button.disabled = true;
                    button.style.opacity = '0.6';
                    button.style.cursor = 'not-allowed';
                });

                setTimeout(function () {
                    buttons.forEach(function (button) {
                        button.disabled = false;
                        button.style.opacity = '';
                        button.style.cursor = '';
                    });
                }, 5000);
            }, true);
        </script>
    </body>
</html>
