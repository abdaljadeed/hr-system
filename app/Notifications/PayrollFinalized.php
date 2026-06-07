<?php

namespace App\Notifications;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PayrollFinalized extends Notification
{
    use Queueable;

    public function __construct(public Payroll $payroll) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payroll.finalized',
            'payroll_id' => $this->payroll->id,
            'period' => $this->payroll->period_label,
            'net_salary' => (float) $this->payroll->net_salary,
            'message' => "Your payslip for {$this->payroll->period_label} is ready. Net salary: {$this->payroll->net_salary_formatted}.",
            'url' => route('payroll.show', $this->payroll),
        ];
    }
}
