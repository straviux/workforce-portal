<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $isCos = $this->input('employee_type') === 'contract_of_service';

        return [
            'employee_no'          => ['nullable', 'string', 'max:100', 'unique:employees,employee_no'],
            'first_name'           => ['required', 'string', 'max:100'],
            'middle_name'          => ['nullable', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'name_extension'       => ['nullable', 'string', 'max:20'],
            'address'              => ['nullable', 'string', 'max:255'],
            'office'               => ['nullable', 'string', 'max:255'],
            'designation'         => ['nullable', 'string', 'max:255'],
            'employee_type'        => ['required', 'in:contract_of_service,project_based'],
            'agency'               => [$isCos ? 'nullable' : 'required', 'string', 'max:255'],
            'amount'               => [$isCos ? 'nullable' : 'required', 'numeric', 'min:0'],
            'contract_ref_no'      => ['nullable', 'string', 'max:100'],
            'atm_account_no'       => ['nullable', 'string', 'max:100'],
            'monthly_compensation' => [$isCos ? 'required' : 'nullable', 'numeric', 'min:0'],
            'deduction_sss'        => ['nullable', 'numeric', 'min:0'],
            'deduction_philhealth' => ['nullable', 'numeric', 'min:0'],
            'deduction_hdmf'       => ['nullable', 'numeric', 'min:0'],
            'is_active'            => ['nullable', 'boolean'],
        ];
    }
}
