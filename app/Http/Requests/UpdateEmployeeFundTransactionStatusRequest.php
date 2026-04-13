<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEmployeeFundTransactionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'transaction_status' => ['required', 'string', 'in:pending,approved,active,denied,suspended'],
            'remarks'            => ['nullable', 'string'],
        ];
    }
}
