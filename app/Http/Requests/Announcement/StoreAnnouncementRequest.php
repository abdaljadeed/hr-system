<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'type' => ['required', Rule::in(['info', 'warning', 'success'])],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
