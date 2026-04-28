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
            'transaction_status' => ['required', 'string', 'in:on_process,claimed,cancelled,suspended,approved,active,denied'],
            'fiscal_year'        => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'obr_no'             => ['nullable', 'string', 'max:100'],
            'remarks'            => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'transaction_status' => $this->normalizeTransactionStatus($this->input('transaction_status')),
        ]);
    }

    private function normalizeTransactionStatus(mixed $status): mixed
    {
        if (is_array($status) && array_key_exists('value', $status)) {
            $status = $status['value'];
        }

        if (!is_string($status)) {
            return $status;
        }

        $normalized = strtolower(trim($status));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        return match ($normalized) {
            'on_process', 'claimed', 'cancelled', 'suspended', 'approved', 'active', 'denied' => $normalized,
            'canceled' => 'cancelled',
            default => $status,
        };
    }
}
