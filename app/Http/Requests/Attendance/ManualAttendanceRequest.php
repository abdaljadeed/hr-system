<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:Y-m-d H:i'],
            'check_out' => ['nullable', 'date_format:Y-m-d H:i', 'after:check_in'],
            'status' => ['required', Rule::in(['present', 'absent', 'late', 'on_leave'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
