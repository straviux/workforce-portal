<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreSwaReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'period_start_date' => ['required', 'date'],
            'period_end_date' => ['required', 'date', 'after_or_equal:period_start_date'],
            'office_head_id' => ['required', Rule::exists('signatories', 'id')->where('part', 'A')],
            'signatory_titles' => ['nullable', 'array'],
            'signatory_titles.*' => ['string', 'max:255'],
            'signatory_show_designation' => ['nullable', 'boolean'],
            'signatory_show_office' => ['nullable', 'boolean'],
            'signatory_info_order' => ['nullable', Rule::in(['designation_first', 'office_first'])],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['required', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'draft_rows' => ['required', 'array', 'size:5'],
            'draft_rows.*.sort_order' => ['required', 'integer', 'between:1,5', 'distinct'],
            'draft_rows.*.daily_values' => ['required', 'array', 'min:1'],
            'draft_rows.*.daily_values.*.work_date' => ['required', 'date'],
            'draft_rows.*.daily_values.*.numeric_value' => ['nullable', 'numeric', 'min:0'],
            'draft_rows.*.daily_values.*.mark_value' => ['nullable', Rule::in(['check', 'dash'])],
        ];
    }
}
