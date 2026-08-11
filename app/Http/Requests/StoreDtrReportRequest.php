<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDtrReportRequest extends FormRequest
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
            'signatory_name_underline' => ['nullable', 'boolean'],
            'signatory_show_designation' => ['nullable', 'boolean'],
            'signatory_show_office' => ['nullable', 'boolean'],
            'signatory_info_order' => ['nullable', Rule::in(['designation_first', 'office_first'])],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['required', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'daily_values' => ['required', 'array', 'min:1'],
            'daily_values.*.work_date' => ['required', 'date'],
            'daily_values.*.am_arrival' => ['nullable', 'date_format:H:i'],
            'daily_values.*.am_departure' => ['nullable', 'date_format:H:i'],
            'daily_values.*.pm_arrival' => ['nullable', 'date_format:H:i'],
            'daily_values.*.pm_departure' => ['nullable', 'date_format:H:i'],
            'daily_values.*.undertime_hours' => ['nullable', 'integer', 'min:0', 'max:23'],
            'daily_values.*.undertime_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
        ];
    }
}
