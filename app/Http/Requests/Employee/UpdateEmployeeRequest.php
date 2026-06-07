<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'job_title' => ['required', 'string', 'max:150'],
            'hire_date' => ['required', 'date'],
            'employment_status' => ['required', Rule::in(['active', 'probation', 'terminated'])],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:500'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
