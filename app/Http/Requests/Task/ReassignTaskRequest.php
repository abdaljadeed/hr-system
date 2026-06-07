<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class ReassignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
