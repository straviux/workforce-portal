<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'event_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in(['legal_holiday', 'local_holiday', 'work_suspension'])],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
