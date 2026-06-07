<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPayrollItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['bonus', 'deduction'])],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
