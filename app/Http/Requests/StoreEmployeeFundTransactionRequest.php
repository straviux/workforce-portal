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
            'payee_name'               => ['required', 'string', 'max:255'],
            'payee_address'            => ['required', 'string', 'max:255'],
            'office'                   => ['required', 'string', 'max:255'],
            'responsibility_center'    => ['required', 'integer', 'exists:responsibility_centers,id'],
            'account_code'             => ['nullable', 'string', 'max:100'],
            'particulars_name'         => ['nullable', 'string', 'max:255'],
            'particulars_description'  => ['nullable', 'string'],
            'amount'                   => ['required', 'numeric', 'min:0'],
            'fiscal_year'              => ['nullable', 'string', 'max:20'],
            'disbursement_type'        => ['nullable', 'string', 'max:100'],
            'explanation'              => ['nullable', 'string'],
            'obr_type'                 => ['nullable', 'string', 'max:100'],
            'obr_no'                   => ['nullable', 'string', 'max:100'],
            'dv_no'                    => ['nullable', 'string', 'max:100'],
            'date_obligated'           => ['nullable', 'date'],
            'transaction_status'       => ['nullable', 'string', 'in:pending,approved,active,denied,suspended'],
            'remarks'                  => ['nullable', 'string'],

            // Contract of Service only
            'employee_id'              => [$isCos ? 'required' : 'nullable', 'string', 'max:100'],
            'contract_ref_no'          => ['nullable', 'string', 'max:100'],
            'swa'                      => ['nullable', 'boolean'],
            'atm_account_no'           => ['nullable', 'string', 'max:100'],
            'monthly_compensation'     => [$isCos ? 'required' : 'nullable', 'numeric', 'min:0'],
            'deduction_sss'            => ['nullable', 'numeric', 'min:0'],
            'deduction_philhealth'     => ['nullable', 'numeric', 'min:0'],
            'deduction_hdmf'           => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => Auth::id(),
        ]);
    }
}
