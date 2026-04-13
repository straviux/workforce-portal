<?php

namespace App\Services;

use App\Models\EmployeeFundTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeFundTransactionService
{
    /**
     * Generate a unique transaction ID for the current month.
     *
     * Format: EFT-YYYYMM-0001
     */
    public function generateTransactionId(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = sprintf('EFT-%s%s-', $year, $month);

        $last = EmployeeFundTransaction::withoutTrashed()
            ->where('transaction_id', 'like', $prefix . '%')
            ->orderBy('transaction_id', 'desc')
            ->first();

        $nextNumber = $last
            ? (int) substr($last->transaction_id, -4) + 1
            : 1;

        return $prefix . sprintf('%04d', $nextNumber);
    }

    /**
     * Create a new employee fund transaction.
     */
    public function create(array $data): EmployeeFundTransaction
    {
        return DB::transaction(function () use ($data) {
            $data['transaction_id'] = $this->generateTransactionId();

            if (empty($data['transaction_status'])) {
                $data['transaction_status'] = 'pending';
            }

            $record = EmployeeFundTransaction::create($data);

            Log::info('employee_fund_transaction_created', [
                'id' => $record->id,
                'transaction_id' => $record->transaction_id,
                'created_by' => $record->created_by,
            ]);

            return $record;
        });
    }

    /**
     * Update an existing employee fund transaction.
     */
    public function update(EmployeeFundTransaction $record, array $data): EmployeeFundTransaction
    {
        $record->update($data);
        $record->refresh();

        return $record;
    }

    /**
     * Update only the transaction status (and optionally remarks).
     */
    public function updateStatus(EmployeeFundTransaction $record, array $data): EmployeeFundTransaction
    {
        $record->update($data);
        $record->refresh();

        Log::info('employee_fund_transaction_status_updated', [
            'id' => $record->id,
            'transaction_status' => $record->transaction_status,
        ]);

        return $record;
    }

    /**
     * Soft-delete a transaction.
     */
    public function delete(EmployeeFundTransaction $record): bool
    {
        $record->delete();
        return true;
    }
}
