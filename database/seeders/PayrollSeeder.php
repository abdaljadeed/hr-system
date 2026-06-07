<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Services\Payroll\PayrollService;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function __construct(private PayrollService $payrollService) {}

    public function run(): void
    {
        $admin = User::role('Admin')->first();
        $prev = now()->subMonth();
        $year = (int) $prev->year;
        $month = (int) $prev->month;

        $employees = Employee::all();
        $payrolls = [];

        foreach ($employees as $employee) {
            try {
                $payrolls[] = $this->payrollService->generate($employee, $year, $month, $admin);
            } catch (\RuntimeException) {
                continue;
            }
        }

        foreach (array_slice($payrolls, 0, 3) as $payroll) {
            $this->payrollService->addBonus($payroll, 'Performance Bonus', 500.00);
            $this->payrollService->finalize($payroll->fresh(), $admin);
        }
    }
}
