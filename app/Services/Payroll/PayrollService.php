<?php

namespace App\Services\Payroll;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Notifications\PayrollFinalized;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PayrollService
{
    public function generate(Employee $employee, int $year, int $month, User $generatedBy): Payroll
    {
        if (Payroll::forEmployee($employee->id)->forMonth($year, $month)->exists()) {
            throw new RuntimeException('Payroll already exists for this period.');
        }

        return DB::transaction(function () use ($employee, $year, $month, $generatedBy) {
            $workingDaysInMonth = $this->countWorkingDaysInMonth($year, $month);
            $baseSalary = (float) $employee->base_salary;
            $dailyRate = $workingDaysInMonth > 0 ? $baseSalary / $workingDaysInMonth : 0;

            $attendances = Attendance::forEmployee($employee->id)
                ->forMonth($year, $month)
                ->get();

            $workedDays = $attendances->whereIn('status', ['present', 'late'])->count();
            $absentDays = $attendances->where('status', 'absent')->count();
            $unpaidLeaveDays = $this->countUnpaidLeaveDays($employee, $year, $month);

            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'period_year' => $year,
                'period_month' => $month,
                'base_salary' => $baseSalary,
                'worked_days' => $workedDays,
                'absent_days' => $absentDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'total_bonuses' => 0,
                'total_deductions' => 0,
                'net_salary' => $baseSalary,
                'status' => 'draft',
                'generated_by' => $generatedBy->id,
            ]);

            if ($absentDays > 0) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'deduction',
                    'label' => 'Absent Days Deduction',
                    'amount' => round($absentDays * $dailyRate, 2),
                ]);
            }

            if ($unpaidLeaveDays > 0) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'deduction',
                    'label' => 'Unpaid Leave Deduction',
                    'amount' => round($unpaidLeaveDays * $dailyRate, 2),
                ]);
            }

            $this->recalculateNet($payroll);

            activity()->causedBy($generatedBy)->performedOn($payroll)
                ->log("Generated payroll for {$employee->full_name} — {$payroll->period_label}");

            return $payroll->fresh(['employee', 'items', 'generatedBy']);
        });
    }

    public function generateBulk(int $year, int $month, User $generatedBy): array
    {
        $results = ['generated' => 0, 'skipped' => 0, 'errors' => []];

        foreach (Employee::where('employment_status', 'active')->get() as $employee) {
            try {
                $this->generate($employee, $year, $month, $generatedBy);
                $results['generated']++;
            } catch (RuntimeException $e) {
                $results['skipped']++;
            }
        }

        return $results;
    }

    public function addBonus(Payroll $payroll, string $label, float $amount): PayrollItem
    {
        $this->guardDraft($payroll);

        $item = PayrollItem::create([
            'payroll_id' => $payroll->id,
            'type' => 'bonus',
            'label' => $label,
            'amount' => $amount,
        ]);

        $this->recalculateNet($payroll);

        return $item;
    }

    public function addDeduction(Payroll $payroll, string $label, float $amount): PayrollItem
    {
        $this->guardDraft($payroll);

        $item = PayrollItem::create([
            'payroll_id' => $payroll->id,
            'type' => 'deduction',
            'label' => $label,
            'amount' => $amount,
        ]);

        $this->recalculateNet($payroll);

        return $item;
    }

    public function removeItem(Payroll $payroll, PayrollItem $item): void
    {
        $this->guardDraft($payroll);

        $item->delete();
        $this->recalculateNet($payroll);
    }

    public function finalize(Payroll $payroll, User $user): Payroll
    {
        $this->guardDraft($payroll);

        $payroll->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        activity()->causedBy($user)->performedOn($payroll)
            ->log("Finalized payroll for {$payroll->employee->full_name}");

        $payroll->employee->user?->notify(new PayrollFinalized($payroll->fresh(['employee', 'items'])));

        return $payroll;
    }

    public function markPaid(Payroll $payroll): Payroll
    {
        if ($payroll->status !== 'finalized') {
            throw new RuntimeException('Only finalized payrolls can be marked as paid.');
        }

        $payroll->update(['status' => 'paid']);

        activity()->causedBy(auth()->user())->performedOn($payroll)
            ->log('Marked payroll as paid');

        return $payroll;
    }

    public function recalculateNet(Payroll $payroll): void
    {
        $totalBonuses = (float) $payroll->items()->where('type', 'bonus')->sum('amount');
        $totalDeductions = (float) $payroll->items()->where('type', 'deduction')->sum('amount');
        $netSalary = (float) $payroll->base_salary - $totalDeductions + $totalBonuses;

        $payroll->update([
            'total_bonuses' => $totalBonuses,
            'total_deductions' => $totalDeductions,
            'net_salary' => max(0, $netSalary),
        ]);
    }

    public function generatePdf(Payroll $payroll): Response
    {
        $payroll->load(['employee.department', 'generatedBy', 'items']);

        $pdf = Pdf::loadView('payroll.partials.payslip-pdf', ['payroll' => $payroll]);

        $filename = sprintf(
            'payslip-%s-%d-%02d.pdf',
            $payroll->employee->employee_code,
            $payroll->period_year,
            $payroll->period_month
        );

        return $pdf->download($filename);
    }

    private function countWorkingDaysInMonth(int $year, int $month): int
    {
        $days = 0;
        $date = Carbon::create($year, $month, 1);

        while ($date->month === $month) {
            if (! $date->isWeekend()) {
                $days++;
            }
            $date->addDay();
        }

        return $days;
    }

    private function countUnpaidLeaveDays(Employee $employee, int $year, int $month): int
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        $unpaidLeaves = LeaveRequest::forEmployee($employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', fn ($q) => $q->where('is_paid', false))
            ->where('start_date', '<=', $monthEnd->toDateString())
            ->where('end_date', '>=', $monthStart->toDateString())
            ->get();

        $days = 0;

        foreach ($unpaidLeaves as $leave) {
            $start = Carbon::parse($leave->start_date)->max($monthStart);
            $end = Carbon::parse($leave->end_date)->min($monthEnd);

            foreach (CarbonPeriod::create($start->toDateString(), $end->toDateString()) as $date) {
                if (! $date->isWeekend()) {
                    $days++;
                }
            }
        }

        return $days;
    }

    private function guardDraft(Payroll $payroll): void
    {
        if (! $payroll->isDraft()) {
            throw new RuntimeException('This payroll is locked and cannot be modified.');
        }
    }
}
