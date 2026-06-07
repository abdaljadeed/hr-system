<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
