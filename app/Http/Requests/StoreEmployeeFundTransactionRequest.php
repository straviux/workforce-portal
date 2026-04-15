<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreEmployeeFundTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $isCos = $this->input('employee_type') === 'contract_of_service';

        return [
            'employee_type'            => ['required', 'in:contract_of_service,project_based'],
            'employee_record_id'       => ['nullable', 'integer', 'exists:employees,id'],
            'payee_name'               => ['required', 'string', 'max:255'],
            'payee_address'            => ['nullable', 'string', 'max:255'],
            'office'                   => ['nullable', 'string', 'max:255'],
            'responsibility_center'    => ['required', 'integer', 'exists:responsibility_centers,id'],
            'particulars_id'           => ['nullable', 'integer', 'exists:particulars,id'],
            'account_code'             => ['nullable', 'string', 'max:100'],
            'particulars_name'         => ['nullable', 'string', 'max:255'],
            'particulars_description'  => ['nullable', 'string'],
            'amount'                   => ['nullable', 'numeric', 'min:0'],
            'fiscal_year'              => ['nullable', 'string', 'max:20'],
            'disbursement_type'        => ['nullable', 'string', 'max:100'],
            'explanation'              => ['nullable', 'string'],
            'obr_type'                 => ['nullable', 'string', 'max:100'],
            'obr_no'                   => ['nullable', 'string', 'max:100'],
            'dv_no'                    => ['nullable', 'string', 'max:100'],
            'date_obligated'           => ['nullable', 'date'],
            'date_from'                => ['nullable', 'date'],
            'date_to'                  => ['nullable', 'date'],
            'transaction_status'       => ['nullable', 'string', 'in:pending,approved,active,denied,suspended'],
            'remarks'                  => ['nullable', 'string'],

            // Contract of Service only
            'employee_id'              => ['nullable', 'string', 'max:100'],
            'contract_ref_no'          => ['nullable', 'string', 'max:100'],
            'swa'                      => ['nullable', 'boolean'],
            'atm_account_no'           => ['nullable', 'string', 'max:100'],
            'monthly_compensation'     => ['nullable', 'numeric', 'min:0'],
            'deduction_sss'            => ['nullable', 'numeric', 'min:0'],
            'deduction_philhealth'     => ['nullable', 'numeric', 'min:0'],
            'deduction_hdmf'           => ['nullable', 'numeric', 'min:0'],

            // Employees list
            'employees'                          => ['nullable', 'array'],
            'employees.*.employee_record_id'     => ['nullable', 'integer', 'exists:employees,id'],
            'employees.*.payee_name'             => ['required_with:employees', 'string', 'max:255'],
            'employees.*.payee_address'          => ['nullable', 'string', 'max:255'],
            'employees.*.office'                 => ['nullable', 'string', 'max:255'],
            'employees.*.employee_id'            => ['nullable', 'string', 'max:100'],
            'employees.*.contract_ref_no'        => ['nullable', 'string', 'max:100'],
            'employees.*.swa'                    => ['nullable', 'boolean'],
            'employees.*.atm_account_no'         => ['nullable', 'string', 'max:100'],
            'employees.*.monthly_compensation'   => ['nullable', 'numeric', 'min:0'],
            'employees.*.deduction_sss'          => ['nullable', 'numeric', 'min:0'],
            'employees.*.deduction_philhealth'   => ['nullable', 'numeric', 'min:0'],
            'employees.*.deduction_hdmf'         => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => Auth::id(),
        ]);
    }
}
