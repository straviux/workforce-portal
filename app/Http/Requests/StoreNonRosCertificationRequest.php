<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreNonRosCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'subject_name' => ['required', 'string', 'max:255'],
            'subject_honorific' => ['nullable', 'string', 'max:50'],
            'designation' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'issued_date' => ['required', 'date'],
            'office_head_id' => [
                'required',
                Rule::exists('signatories', 'id')->where(fn($query) => $query->where('part', 'A')),
            ],
        ];
    }
}
