<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    @php
        $cards = [
            ['label' => 'Active Employees', 'accent' => 'indigo', 'format' => 'int', 'good' => 'up',
             'cur' => $stats['total_employees'], 'prev' => $stats['total_employees_prev'],
             'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
            ['label' => 'Present Today', 'accent' => 'green', 'format' => 'int', 'good' => 'up',
             'cur' => $stats['present_today'], 'prev' => $stats['present_today_prev'],
             'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Pending Leaves', 'accent' => 'yellow', 'format' => 'int', 'good' => 'down',
             'cur' => $stats['pending_leaves'], 'prev' => $stats['pending_leaves_prev'],
             'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['label' => 'Open Tasks', 'accent' => 'blue', 'format' => 'int', 'good' => 'down',
             'cur' => $stats['open_tasks'], 'prev' => $stats['open_tasks_prev'],
             'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['label' => 'Monthly Payroll', 'accent' => 'purple', 'format' => 'money', 'good' => null,
             'cur' => $stats['this_month_payroll_total'], 'prev' => null,
             'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        $accents = [
            'indigo' => 'bg-indigo-50 text-indigo-600',
            'green'  => 'bg-green-50 text-green-600',
            'yellow' => 'bg-yellow-50 text-yellow-600',
            'blue'   => 'bg-blue-50 text-blue-600',
            'purple' => 'bg-purple-50 text-purple-600',
        ];
    @endphp

    <div class="space-y-6">
        @foreach($announcements as $announcement)
            @php
                $annStyle = match($announcement->type) {
                    'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
                    'success' => 'border-green-200 bg-green-50 text-green-800',
                    default   => 'border-blue-200 bg-blue-50 text-blue-800',
                };
            @endphp
            <div class="flex items-start justify-between gap-4 rounded-lg border px-4 py-3 {{ $annStyle }}">
                <div class="min-w-0">
                    <p class="text-sm font-semibold">{{ $announcement->title }}</p>
                    <p class="mt-0.5 text-sm">{{ $announcement->body }}</p>
                </div>
                <form method="POST" action="{{ route('announcements.dismiss', $announcement) }}">
                    @csrf
                    <button type="submit" class="shrink-0 text-lg leading-none opacity-60 hover:opacity-100">&times;</button>
                </form>
            </div>
        @endforeach

        @if($isManager)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach($cards as $card)
                    @php
                        $value = $card['format'] === 'money' ? number_format($card['cur'], 2) : number_format($card['cur']);
                        $trend = null;
                        if (! is_null($card['prev'])) {
                            $diff = $card['cur'] - $card['prev'];
                            $pct = $card['prev'] > 0 ? round(abs($diff) / $card['prev'] * 100) : ($card['cur'] > 0 ? 100 : 0);
                            $improved = $card['good'] === 'up' ? $diff > 0 : $diff < 0;
                            $trend = [
                                'arrow' => $diff > 0 ? '↑' : ($diff < 0 ? '↓' : '→'),
                                'pct' => $pct,
                                'class' => $diff === 0 ? 'bg-gray-100 text-gray-500' : ($improved ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'),
                            ];
                        }
                    @endphp
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="flex items-start justify-between gap-2">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $accents[$card['accent']] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                                </svg>
                            </span>
                            @if($trend)
                                <span class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $trend['class'] }}">
                                    {{ $trend['arrow'] }} {{ $trend['pct'] }}%
                                </span>
                            @endif
                        </div>
                        <div class="mt-3">
                            <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @unless($isManager)
                <div class="lg:col-span-1">
                    @include('attendance.partials.check-in-widget')
                </div>
            @endunless

            <div class="rounded-lg bg-white p-6 shadow {{ $isManager ? 'lg:col-span-2' : 'lg:col-span-2' }}">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Attendance Trend &middot; Last 30 Days</h3>
                <div class="h-64"><canvas id="attendanceTrendChart"></canvas></div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-700">Tasks by Status</h3>
                <div class="h-64"><canvas id="tasksByStatusChart"></canvas></div>
            </div>
        </div>

        @if($isManager)
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-sm font-semibold text-gray-700">Approved Leave by Type &middot; This Month</h3>
                    <div class="h-64"><canvas id="leavesByTypeChart"></canvas></div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-sm font-semibold text-gray-700">Top Employees &middot; Approved Tasks This Month</h3>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 text-left">Employee</th>
                                <th class="py-2 text-left">Department</th>
                                <th class="py-2 text-right">Approved Tasks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($topEmployees as $row)
                                <tr>
                                    <td class="py-2.5 font-medium text-gray-900">{{ $row['name'] }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $row['department'] }}</td>
                                    <td class="py-2.5 text-right">
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            {{ $row['count'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-gray-400">No approved tasks this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($showCelebrations)
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <svg class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z M12 7v6 M9 10h6" /></svg>
                        Birthdays This Month
                    </h3>
                    <div class="divide-y divide-gray-100">
                        @forelse($birthdays as $person)
                            <div class="flex items-center justify-between py-2.5">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $person['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $person['date'] }}</p>
                                </div>
                                <span class="rounded-full bg-pink-50 px-2.5 py-0.5 text-xs font-medium text-pink-700">
                                    @if($person['days_until'] === 0) Today 🎉
                                    @elseif($person['days_until'] > 0) in {{ $person['days_until'] }} day{{ $person['days_until'] === 1 ? '' : 's' }}
                                    @else passed @endif
                                </span>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-gray-400">No birthdays this month.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                        Work Anniversaries
                    </h3>
                    <div class="divide-y divide-gray-100">
                        @forelse($anniversaries as $person)
                            <div class="flex items-center justify-between py-2.5">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $person['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $person['date'] }}</p>
                                </div>
                                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                    {{ $person['years'] }} year{{ $person['years'] === 1 ? '' : 's' }}
                                </span>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-gray-400">No anniversaries this month.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const palette = {
                    indigo: '#6366f1', green: '#22c55e', red: '#ef4444',
                    yellow: '#eab308', blue: '#3b82f6', gray: '#9ca3af',
                    purple: '#a855f7', orange: '#f97316',
                };

                Chart.defaults.font.family = 'Figtree, ui-sans-serif, system-ui, sans-serif';
                Chart.defaults.color = '#6b7280';

                const trend = @json($attendanceTrend);
                new Chart(document.getElementById('attendanceTrendChart'), {
                    type: 'line',
                    data: {
                        labels: trend.labels,
                        datasets: [
                            { label: 'Present', data: trend.present, borderColor: palette.green,
                              backgroundColor: 'rgba(34,197,94,0.1)', tension: 0.3, fill: true },
                            { label: 'Absent', data: trend.absent, borderColor: palette.red,
                              backgroundColor: 'rgba(239,68,68,0.1)', tension: 0.3, fill: true },
                        ],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    },
                });

                const tasks = @json($tasksByStatus);
                const taskColors = {
                    todo: palette.gray, in_progress: palette.yellow, submitted: palette.blue,
                    approved: palette.green, rejected: palette.red,
                };
                const taskKeys = Object.keys(tasks);
                new Chart(document.getElementById('tasksByStatusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: taskKeys.map(k => k.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())),
                        datasets: [{
                            data: taskKeys.map(k => tasks[k]),
                            backgroundColor: taskKeys.map(k => taskColors[k]),
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    },
                });

                const leavesCanvas = document.getElementById('leavesByTypeChart');
                if (leavesCanvas) {
                    const leaves = @json($leavesByType);
                    new Chart(leavesCanvas, {
                        type: 'bar',
                        data: {
                            labels: leaves.map(l => l.name),
                            datasets: [{
                                label: 'Days',
                                data: leaves.map(l => l.days),
                                backgroundColor: leaves.map(l => palette[l.color] || palette.blue),
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        },
                    });
                }
            })();
        </script>
    @endpush
</x-app-layout>
