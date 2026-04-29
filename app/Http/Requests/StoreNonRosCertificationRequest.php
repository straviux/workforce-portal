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
            'subject_honorific' => ['nullable', 'string', 'max:50'],
            'subject_firstname' => ['required', 'string', 'max:255'],
            'subject_middlename' => ['nullable', 'string', 'max:255'],
            'subject_lastname' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'office' => ['required', 'string', 'max:255'],
            'issued_date' => ['required', 'date'],
            'signatory_titles' => ['nullable', 'array'],
            'signatory_titles.*' => ['string', 'max:255'],
            'signatory_name_underline' => ['nullable', 'boolean'],
            'signatory_show_designation' => ['nullable', 'boolean'],
            'signatory_show_office' => ['nullable', 'boolean'],
            'signatory_info_order' => ['nullable', Rule::in(['designation_first', 'office_first'])],
            'office_head_id' => [
                'required',
                Rule::exists('signatories', 'id')->where(fn($query) => $query->where('part', 'A')),
            ],
        ];
    }
}
