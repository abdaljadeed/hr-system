<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id;

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('departments', 'name')->ignore($departmentId)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($departmentId)->whereNull('deleted_at')],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
