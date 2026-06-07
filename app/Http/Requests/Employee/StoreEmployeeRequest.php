<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code'],
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

            'provision_user' => ['boolean'],
            'email' => ['required_if:provision_user,1', 'nullable', 'email', 'unique:users,email'],
            'password' => ['required_if:provision_user,1', 'nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required_if:provision_user,1', 'nullable', 'string', 'exists:roles,name'],
        ];
    }
}
