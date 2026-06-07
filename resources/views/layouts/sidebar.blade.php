@php
    $linkBase = 'flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition';
    $linkIdle = 'text-gray-300 hover:bg-gray-800 hover:text-white';
    $linkActive = 'bg-gray-800 text-white';
    $groupLabel = 'px-4 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-gray-500';

    $isActive = fn(string $pattern) => request()->routeIs($pattern) ? $linkActive : $linkIdle;
@endphp

<aside
    x-cloak
    x-show="$store.sidebar.open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900"
>
    <div class="flex h-16 items-center px-6 border-b border-gray-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-white">
            <x-application-logo class="h-8 w-8 fill-current text-white" />
            <span class="text-lg font-semibold">{{ config('app.name', 'HR System') }}</span>
        </a>
    </div>

    <nav class="flex flex-col gap-1 px-3 pb-4">
        <p class="{{ $groupLabel }}">Main</p>
        <a href="{{ route('dashboard') }}"
           class="{{ $linkBase }} {{ $isActive('dashboard') }}">
            Dashboard
        </a>
        @can('announcements.manage')
            <a href="{{ route('announcements.index') }}"
               class="{{ $linkBase }} {{ $isActive('announcements.*') }}">
                Announcements
            </a>
        @endcan

        @canany(['employees.view', 'departments.view'])
            <p class="{{ $groupLabel }}">People</p>
            @can('employees.view')
                <a href="{{ route('employees.index') }}"
                   class="{{ $linkBase }} {{ $isActive('employees.*') }}">
                    Employees
                </a>
            @endcan
            @can('departments.view')
                <a href="{{ route('departments.index') }}"
                   class="{{ $linkBase }} {{ $isActive('departments.*') }}">
                    Departments
                </a>
            @endcan
        @endcanany

        @canany(['attendance.view', 'leaves.view', 'tasks.view'])
            <p class="{{ $groupLabel }}">Operations</p>
            @can('attendance.view')
                <a href="{{ route('attendance.index') }}"
                   class="{{ $linkBase }} {{ $isActive('attendance.*') }}">
                    Attendance
                </a>
            @endcan
            @can('leaves.view')
                <a href="{{ route('leaves.index') }}"
                   class="{{ $linkBase }} {{ $isActive('leaves.*') }}">
                    Leave Management
                </a>
            @endcan
            @can('tasks.view')
                <a href="{{ route('tasks.index') }}"
                   class="{{ $linkBase }} {{ $isActive('tasks.*') }}">
                    Tasks
                </a>
            @endcan
        @endcanany

        @if(auth()->user()->can('payroll.view') || auth()->user()->hasAnyRole(['Admin', 'HR Manager']))
            <p class="{{ $groupLabel }}">Finance</p>
            @can('payroll.view')
                <a href="{{ route('payroll.index') }}"
                   class="{{ $linkBase }} {{ $isActive('payroll.*') }}">
                    Payroll
                </a>
            @endcan
            @hasanyrole('Admin|HR Manager')
                <a href="{{ route('reports.index') }}"
                   class="{{ $linkBase }} {{ $isActive('reports.*') }}">
                    Reports
                </a>
            @endhasanyrole
        @endif

        @canany(['activitylog.view', 'users.manage'])
            <p class="{{ $groupLabel }}">System</p>
            @can('activitylog.view')
                <a href="{{ route('activity.index') }}"
                   class="{{ $linkBase }} {{ $isActive('activity.*') }}">
                    Activity Log
                </a>
            @endcan
            @can('users.manage')
                <a href="#" class="{{ $linkBase }} {{ $linkIdle }}">Users &amp; Roles</a>
            @endcan
        @endcanany
    </nav>
</aside>
