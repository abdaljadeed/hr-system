<x-app-layout>
    @section('breadcrumb')
        <x-breadcrumb :items="[['label' => 'Payroll']]" />
    @endsection

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Payroll</h2>
        </div>
    </x-slot>

    @php
        $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = \Carbon\Carbon::create($year, $month, 1)->addMonth();
        $periodLabel = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('payroll.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">&larr;</a>
            <span class="min-w-36 text-center text-base font-semibold text-gray-800">{{ $periodLabel }}</span>
            <a href="{{ route('payroll.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">&rarr;</a>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @hasanyrole('Admin|HR Manager')
                <form method="POST" action="{{ route('reports.excel') }}">
                    @csrf
                    <input type="hidden" name="report_type" value="payroll">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Export Excel
                    </button>
                </form>
            @endhasanyrole

            @can('create', \App\Models\Payroll::class)
                <form method="POST" action="{{ route('payroll.store-bulk') }}"
                      onsubmit="return confirm('Generate payroll for ALL active employees for {{ $periodLabel }}?');">
                    @csrf
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Generate All for {{ $periodLabel }}
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @can('payroll.generate')
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Generate Individual Payroll</h3>
            <form method="POST" action="{{ route('payroll.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="year"  value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <div>
                    <x-input-label for="employee_id" value="Employee" />
                    <select id="employee_id" name="employee_id" required
                            class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Select —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('employee_id')" class="mt-1" />
                </div>
                <x-primary-button>Generate</x-primary-button>
            </form>
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Employee</th>
                        <th class="px-6 py-3 text-left">Department</th>
                        <th class="px-6 py-3 text-right">Base Salary</th>
                        <th class="px-6 py-3 text-right">Net Salary</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @php $totalNet = 0; @endphp
                    @foreach($employees as $emp)
                        @php
                            $payroll = $emp->payrolls->first();
                            if ($payroll) $totalNet += (float) $payroll->net_salary;
                            $badge = $payroll ? match($payroll->status) {
                                'draft'     => 'bg-yellow-100 text-yellow-800',
                                'finalized' => 'bg-blue-100 text-blue-800',
                                'paid'      => 'bg-green-100 text-green-800',
                            } : '';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $emp->full_name }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $emp->department?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right text-gray-700">
                                {{ $payroll ? number_format((float)$payroll->base_salary, 2) : number_format((float)$emp->base_salary, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                {{ $payroll ? $payroll->net_salary_formatted : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($payroll)
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                        {{ ucfirst($payroll->status) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Not generated</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($payroll)
                                    <a href="{{ route('payroll.show', $payroll) }}"
                                       class="text-indigo-600 hover:text-indigo-900">View</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                        <td class="px-6 py-3 text-gray-700" colspan="3">Total Net Payroll</td>
                        <td class="px-6 py-3 text-right text-gray-900">{{ number_format($totalNet, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>

    @else
        <div class="space-y-4">
            @forelse($employees->first()?->payrolls ?? collect() as $payroll)
                @php
                    $badge = match($payroll->status) {
                        'draft'     => 'bg-yellow-100 text-yellow-800',
                        'finalized' => 'bg-blue-100 text-blue-800',
                        'paid'      => 'bg-green-100 text-green-800',
                    };
                @endphp
                <div class="flex items-center justify-between rounded-lg bg-white px-6 py-4 shadow">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $payroll->period_label }}</p>
                        <p class="text-sm text-gray-500">Net: <span class="font-medium text-gray-900">{{ $payroll->net_salary_formatted }}</span></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">{{ ucfirst($payroll->status) }}</span>
                        <a href="{{ route('payroll.show', $payroll) }}"
                           class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                    </div>
                </div>
            @empty
                <div class="rounded-lg bg-white px-6 py-10 text-center text-gray-400 shadow">No payslips available yet.</div>
            @endforelse
        </div>
    @endcan
</x-app-layout>
