<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
