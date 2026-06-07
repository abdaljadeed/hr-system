<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payroll.view');
    }

    public function view(User $user, Payroll $payroll): bool
    {
        if ($user->can('payroll.generate')) {
            return true;
        }

        return $user->employee?->id === $payroll->employee_id
            && in_array($payroll->status, ['finalized', 'paid']);
    }

    public function create(User $user): bool
    {
        return $user->can('payroll.generate');
    }

    public function addItem(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.generate') && $payroll->isDraft();
    }

    public function removeItem(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.generate') && $payroll->isDraft();
    }

    public function finalize(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.generate') && $payroll->isDraft();
    }

    public function markPaid(User $user, Payroll $payroll): bool
    {
        return $user->can('payroll.generate') && $payroll->status === 'finalized';
    }

    public function downloadPdf(User $user, Payroll $payroll): bool
    {
        if ($user->can('payroll.generate')) {
            return true;
        }

        return $user->employee?->id === $payroll->employee_id
            && in_array($payroll->status, ['finalized', 'paid']);
    }
}
