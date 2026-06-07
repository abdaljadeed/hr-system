<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Reports']]" />
    @endsection

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reports &amp; Exports</h2>
    </x-slot>

    @php
        $field = 'mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
        $previewBtn = 'rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50';
        $excelBtn = 'rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700';
        $pdfBtn = 'rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700';
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Attendance Report --}}
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-base font-semibold text-gray-900">Attendance Report</h3>
            <p class="mt-1 text-sm text-gray-500">Daily check-in/out records with worked hours and status for a month.</p>
            <form method="POST" action="{{ route('reports.excel') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="report_type" value="attendance">
                <div>
                    <x-input-label value="Employee (optional)" />
                    <select name="employee_id" class="{{ $field }}">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Month" />
                        <select name="month" class="{{ $field }}">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($m === $currentMonth)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Year" />
                        <select name="year" class="{{ $field }}">
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected($y === $currentYear)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" formaction="{{ route('reports.preview') }}" formmethod="GET" formtarget="_blank" class="{{ $previewBtn }}">Preview</button>
                    <button type="submit" class="{{ $excelBtn }}">Export Excel</button>
                    <button type="submit" formaction="{{ route('reports.pdf') }}" class="{{ $pdfBtn }}">Export PDF</button>
                </div>
            </form>
        </div>

        {{-- Payroll Report --}}
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-base font-semibold text-gray-900">Payroll Report</h3>
            <p class="mt-1 text-sm text-gray-500">Monthly payslip totals across all employees with total payroll cost.</p>
            <form method="POST" action="{{ route('reports.excel') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="report_type" value="payroll">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Month" />
                        <select name="month" class="{{ $field }}">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($m === $currentMonth)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Year" />
                        <select name="year" class="{{ $field }}">
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected($y === $currentYear)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" formaction="{{ route('reports.preview') }}" formmethod="GET" formtarget="_blank" class="{{ $previewBtn }}">Preview</button>
                    <button type="submit" class="{{ $excelBtn }}">Export Excel</button>
                    <button type="submit" formaction="{{ route('reports.pdf') }}" class="{{ $pdfBtn }}">Export PDF</button>
                </div>
            </form>
        </div>

        {{-- Employee List --}}
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-base font-semibold text-gray-900">Employee List</h3>
            <p class="mt-1 text-sm text-gray-500">Directory of employees filtered by department and employment status.</p>
            <form method="POST" action="{{ route('reports.excel') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="report_type" value="employees">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Department" />
                        <select name="department_id" class="{{ $field }}">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Status" />
                        <select name="status" class="{{ $field }}">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="probation">Probation</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="{{ $excelBtn }}">Export Excel</button>
                </div>
            </form>
        </div>

        {{-- Leave Report --}}
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-base font-semibold text-gray-900">Leave Report</h3>
            <p class="mt-1 text-sm text-gray-500">Leave requests filtered by employee, type, status, and month.</p>
            <form method="POST" action="{{ route('reports.excel') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="report_type" value="leaves">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label value="Employee (optional)" />
                        <select name="employee_id" class="{{ $field }}">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Leave Type" />
                        <select name="leave_type_id" class="{{ $field }}">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Status" />
                        <select name="status" class="{{ $field }}">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-input-label value="Month" />
                            <select name="month" class="{{ $field }}">
                                <option value="">Any</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('M') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Year" />
                            <select name="year" class="{{ $field }}">
                                <option value="">Any</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="{{ $excelBtn }}">Export Excel</button>
                </div>
            </form>
        </div>

        {{-- Employee Performance --}}
        <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-900">Employee Performance</h3>
            <p class="mt-1 text-sm text-gray-500">Per-employee attendance, leave, and task summary for a period.</p>
            <form method="POST" action="{{ route('reports.pdf') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="report_type" value="performance">
                <div class="min-w-56">
                    <x-input-label value="Employee" />
                    <select name="employee_id" required class="{{ $field }}">
                        <option value="">— Select —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Month" />
                    <select name="month" class="{{ $field }}">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $currentMonth)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <x-input-label value="Year" />
                    <select name="year" class="{{ $field }}">
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected($y === $currentYear)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" formaction="{{ route('reports.preview') }}" formmethod="GET" formtarget="_blank" class="{{ $previewBtn }}">Preview</button>
                    <button type="submit" class="{{ $pdfBtn }}">Export PDF</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
