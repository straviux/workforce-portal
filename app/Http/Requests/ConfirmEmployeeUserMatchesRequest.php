<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmEmployeeUserMatchesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'matches' => ['required', 'array', 'min:1'],
            'matches.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'matches.*.user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
