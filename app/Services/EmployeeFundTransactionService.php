<?php

namespace App\Services;

use App\Models\EmployeeFundTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

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

        $last = EmployeeFundTransaction::withTrashed()
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
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () use ($data) {
                    $employees = $data['employees'] ?? [];
                    unset($data['employees']);

                    $data['transaction_id'] = $this->generateTransactionId();

                    if (empty($data['transaction_status'])) {
                        $data['transaction_status'] = 'on_process';
                    }

                    $record = EmployeeFundTransaction::create($data);

                    foreach ($employees as $empData) {
                        $record->employees()->create($empData);
                    }

                    Log::info('employee_fund_transaction_created', [
                        'id'               => $record->id,
                        'transaction_id'   => $record->transaction_id,
                        'employees_count'  => count($employees),
                        'created_by'       => $record->created_by,
                    ]);

                    return $record->load('employees.employeeRecord');
                });
            } catch (QueryException $exception) {
                if (!$this->isDuplicateTransactionIdException($exception) || $attempt === $maxAttempts) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Could not generate a unique transaction ID.');
    }

    /**
     * Update an existing employee fund transaction.
     */
    public function update(EmployeeFundTransaction $record, array $data): EmployeeFundTransaction
    {
        $employees = array_key_exists('employees', $data) ? $data['employees'] : null;
        unset($data['employees']);

        $record->update($data);

        if ($employees !== null) {
            $record->employees()->delete();
            foreach ($employees as $empData) {
                $record->employees()->create($empData);
            }
        }

        return $record->refresh()->load('employees.employeeRecord');
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

    private function isDuplicateTransactionIdException(QueryException $exception): bool
    {
        return $exception->getCode() === '23000'
            && str_contains($exception->getMessage(), 'employee_fund_transactions_transaction_id_unique');
    }
}
