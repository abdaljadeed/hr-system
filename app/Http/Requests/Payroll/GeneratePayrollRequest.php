<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
