<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Task;
use App\Models\User;
use App\Services\Payroll\PayrollService;
use App\Services\Task\TaskService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function __construct(
        private PayrollService $payrollService,
        private TaskService $taskService,
    ) {}

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::where('email', 'admin@hr.test')->first();

        $departments = collect([
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Sales', 'code' => 'SAL'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
        ])->mapWithKeys(fn ($dept) => [
            $dept['name'] => Department::create([
                'name' => $dept['name'],
                'code' => $dept['code'],
                'description' => "{$dept['name']} department.",
            ]),
        ]);

        $employees = $this->seedEmployees($departments);

        $this->call(LeaveSeeder::class);
        $this->seedAttendance($employees);
        $this->seedLeaveRequests($employees);
        $this->seedPayrolls($employees, $admin);
        $this->seedTasks($employees, $admin);
        $this->seedAnnouncements($admin);

        $this->report();
    }

    private function seedEmployees($departments): Collection
    {
        $people = [
            ['Hala', 'Mansour', 'female', '1990-03-15', 'Human Resources', 'HR Manager', '2021-02-01', 13000, 'HR Manager', 'hr@hr.test'],
            ['Tariq', 'Saleh', 'male', '1986-06-09', 'Engineering', 'Engineering Lead', '2020-05-10', 16000, 'Team Lead', 'lead@hr.test'],
            ['Omar', 'Khalil', 'male', '1995-06-21', 'Engineering', 'Software Developer', '2023-03-15', 9500, 'Employee', 'employee@hr.test'],
            ['Khalid', 'Al-Rashid', 'male', '1991-09-12', 'Engineering', 'Backend Developer', '2020-06-15', 11000, 'Employee', null],
            ['Layla', 'Haddad', 'female', '1994-11-30', 'Engineering', 'Frontend Developer', '2022-08-01', 9000, 'Employee', null],
            ['Yousef', 'Nasser', 'male', '1989-01-18', 'Engineering', 'DevOps Engineer', '2021-11-20', 12000, 'Employee', null],
            ['Noura', 'Aziz', 'female', '1996-06-05', 'Human Resources', 'HR Specialist', '2023-01-09', 7500, 'Employee', null],
            ['Sami', 'Darwish', 'male', '1992-04-25', 'Human Resources', 'Recruiter', '2022-03-14', 7000, 'Employee', null],
            ['Rana', 'Fares', 'female', '1987-07-07', 'Sales', 'Sales Lead', '2019-09-02', 14000, 'Team Lead', null],
            ['Bilal', 'Hamdan', 'male', '1993-02-11', 'Sales', 'Sales Executive', '2021-06-21', 8000, 'Employee', null],
            ['Dana', 'Saab', 'female', '1997-12-03', 'Sales', 'Account Manager', '2023-05-18', 7800, 'Employee', null],
            ['Faisal', 'Murad', 'male', '1985-10-29', 'Finance', 'Finance Lead', '2018-04-12', 15000, 'Team Lead', null],
            ['Maya', 'Rizk', 'female', '1990-08-16', 'Finance', 'Accountant', '2021-10-05', 9500, 'Employee', null],
            ['Ziad', 'Habib', 'male', '1988-03-22', 'Marketing', 'Marketing Lead', '2020-01-27', 13000, 'Team Lead', null],
            ['Salma', 'Younis', 'female', '1995-06-14', 'Marketing', 'Marketing Specialist', '2022-12-01', 7200, 'Employee', null],
        ];

        $employees = collect();

        foreach ($people as $index => $person) {
            [$first, $last, $gender, $dob, $deptName, $job, $hire, $salary, $role, $email] = $person;

            $email ??= strtolower("{$first}.{$last}@hr.test");
            $email = str_replace(['-', ' '], '', $email);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "{$first} {$last}",
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$role]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'department_id' => $departments[$deptName]->id,
                'employee_code' => sprintf('EMP-2026-%04d', $index + 1),
                'first_name' => $first,
                'last_name' => $last,
                'phone' => sprintf('+966 5%d %03d %04d', rand(0, 9), rand(0, 999), rand(0, 9999)),
                'gender' => $gender,
                'date_of_birth' => $dob,
                'job_title' => $job,
                'hire_date' => $hire,
                'employment_status' => 'active',
                'base_salary' => $salary,
            ]);

            if ($role === 'Team Lead' || $role === 'HR Manager') {
                $departments[$deptName]->update(['manager_id' => $employee->id]);
            }

            $employees->push($employee);
        }

        return $employees;
    }

    private function seedAttendance($employees): void
    {
        $end = Carbon::yesterday();
        $start = $end->copy()->subMonths(6);

        foreach ($employees as $employee) {
            $pool = $this->statusPool();
            $rows = [];

            foreach (CarbonPeriod::create($start, $end) as $date) {
                if ($date->isWeekend()) {
                    continue;
                }

                $status = $pool[array_rand($pool)];
                $checkIn = null;
                $checkOut = null;
                $workedHours = null;

                if (in_array($status, ['present', 'late'], true)) {
                    $checkIn = $date->copy()->setTime($status === 'late' ? rand(9, 10) : rand(7, 8), rand(0, 59));
                    $checkOut = $date->copy()->setTime(rand(16, 18), rand(0, 59));
                    $workedHours = round($checkIn->diffInMinutes($checkOut) / 60, 2);
                }

                $rows[] = [
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'worked_hours' => $workedHours,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($rows, 200) as $chunk) {
                Attendance::insert($chunk);
            }
        }
    }

    private function seedLeaveRequests($employees): void
    {
        $annual = LeaveType::where('code', 'annual')->first();
        $sick = LeaveType::where('code', 'sick')->first();
        $reviewer = User::role('HR Manager')->first();

        $plans = [
            [3, $annual, -40, 4, 'approved', 'Family vacation.'],
            [5, $sick, -25, 2, 'approved', 'Flu recovery.'],
            [6, $annual, -15, 3, 'approved', 'Personal time off.'],
            [8, $sick, 6, 1, 'pending', 'Medical appointment.'],
            [10, $annual, 12, 5, 'pending', 'Trip abroad.'],
            [11, $annual, 20, 2, 'pending', 'Wedding.'],
            [13, $sick, -8, 1, 'rejected', 'Insufficient notice.'],
            [14, $annual, 30, 3, 'rejected', 'Team capacity.'],
        ];

        foreach ($plans as [$idx, $type, $offset, $days, $status, $reason]) {
            $employee = $employees[$idx] ?? null;

            if (! $employee || ! $type) {
                continue;
            }

            $startDate = Carbon::today()->addDays($offset);
            $endDate = $startDate->copy()->addDays($days - 1);

            LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $type->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'days' => $days,
                'reason' => $reason,
                'status' => $status,
                'reviewed_by' => in_array($status, ['approved', 'rejected'], true) ? $reviewer?->id : null,
                'reviewed_at' => in_array($status, ['approved', 'rejected'], true) ? now()->subDays(rand(1, 10)) : null,
                'review_notes' => $status === 'rejected' ? $reason : null,
            ]);

            if ($status === 'approved' && $type->is_paid) {
                LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('year', $startDate->year)
                    ->increment('used_days', $days);
            }
        }
    }

    private function seedPayrolls($employees, ?User $admin): void
    {
        foreach ([1, 2, 3] as $monthsAgo) {
            $period = now()->subMonths($monthsAgo);

            foreach ($employees as $employee) {
                try {
                    $payroll = $this->payrollService->generate($employee, (int) $period->year, (int) $period->month, $admin);
                    $this->payrollService->finalize($payroll->fresh(), $admin);
                } catch (RuntimeException) {
                    continue;
                }
            }
        }
    }

    private function seedTasks($employees, ?User $admin): void
    {
        $leads = User::role('Team Lead')->get();
        $assignees = $employees->filter(fn ($e) => $e->user?->hasRole('Employee'))->pluck('user')->values();

        $tasks = [
            ['Set up CI pipeline', 'high', 'todo'],
            ['Refactor authentication module', 'medium', 'in_progress'],
            ['Write API documentation', 'low', 'submitted'],
            ['Fix login redirect bug', 'high', 'approved'],
            ['Design new landing page', 'medium', 'in_progress'],
            ['Migrate database to MySQL', 'high', 'todo'],
            ['Prepare Q3 sales report', 'medium', 'submitted'],
            ['Onboard new hires', 'low', 'approved'],
            ['Review payroll discrepancies', 'high', 'in_progress'],
            ['Update employee handbook', 'low', 'todo'],
            ['Launch email campaign', 'medium', 'approved'],
            ['Optimize dashboard queries', 'high', 'submitted'],
            ['Conduct security audit', 'high', 'todo'],
            ['Plan team offsite', 'low', 'in_progress'],
            ['Negotiate vendor contract', 'medium', 'rejected'],
            ['Build reporting export', 'medium', 'approved'],
            ['Fix mobile layout issues', 'low', 'todo'],
            ['Draft marketing budget', 'medium', 'submitted'],
            ['Interview backend candidates', 'high', 'in_progress'],
            ['Archive old records', 'low', 'approved'],
        ];

        foreach ($tasks as $i => [$title, $priority, $target]) {
            $assignee = $assignees[$i % $assignees->count()];
            $creator = $leads[$i % $leads->count()] ?? $admin;

            $task = $this->taskService->create([
                'title' => $title,
                'description' => "{$title} — auto-generated demo task.",
                'assigned_to' => $assignee->id,
                'priority' => $priority,
                'due_date' => now()->addDays(rand(-5, 20))->toDateString(),
            ], $creator);

            $this->progressTask($task, $target, $assignee, $creator);
        }
    }

    private function progressTask(Task $task, string $target, User $assignee, User $reviewer): void
    {
        if ($target === 'todo') {
            return;
        }

        $this->taskService->changeStatus($task, 'in_progress', $assignee);

        if ($target === 'in_progress') {
            return;
        }

        $this->taskService->changeStatus($task->fresh(), 'submitted', $assignee);

        if ($target === 'submitted') {
            return;
        }

        $decision = $target === 'rejected' ? 'rejected' : 'approved';
        $this->taskService->changeStatus($task->fresh(), $decision, $reviewer,
            $decision === 'approved' ? 'Approved — looks good.' : 'Needs rework.');
    }

    private function seedAnnouncements(?User $admin): void
    {
        Announcement::create([
            'title' => 'Welcome to the new HR portal',
            'body' => 'Explore your dashboard, request leave, and view payslips all in one place.',
            'type' => 'info',
            'published_by' => $admin->id,
            'is_active' => true,
        ]);

        Announcement::create([
            'title' => 'Payroll cut-off this Thursday',
            'body' => 'Please submit all overtime and expense claims before Thursday 5 PM.',
            'type' => 'warning',
            'published_by' => $admin->id,
            'expires_at' => now()->addWeeks(2),
            'is_active' => true,
        ]);
    }

    private function statusPool(): array
    {
        $weights = ['present' => 72, 'late' => 12, 'absent' => 10, 'on_leave' => 6];
        $pool = [];

        foreach ($weights as $status => $weight) {
            $pool = array_merge($pool, array_fill(0, $weight, $status));
        }

        return $pool;
    }

    private function report(): void
    {
        $this->command?->info('');
        $this->command?->info('Demo data seeded. Login credentials (password: password):');
        $this->command?->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@hr.test', 'password'],
                ['HR Manager', 'hr@hr.test', 'password'],
                ['Team Lead', 'lead@hr.test', 'password'],
                ['Employee', 'employee@hr.test', 'password'],
            ]
        );
    }
}
