<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeFundTransactionRequest;
use App\Http\Requests\UpdateEmployeeFundTransactionRequest;
use App\Http\Requests\UpdateEmployeeFundTransactionStatusRequest;
use App\Models\EmployeeFundTransaction;
use App\Services\EmployeeFundTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeFundTransactionController extends Controller
{
    public function __construct(
        private EmployeeFundTransactionService $service,
    ) {}

    /**
     * List transactions with optional server-side filtering.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EmployeeFundTransaction::with(['creator', 'responsibilityCenter', 'employeeRecord', 'employees.employeeRecord'])->latest();

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('payee_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('dv_no', 'like', "%{$search}%")
                        ->orWhere('obr_no', 'like', "%{$search}%")
                        ->orWhereHas('creator', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            }

            if ($status = $request->get('status')) {
                $query->where('transaction_status', $status);
            }

            if ($fiscalYear = $request->get('fiscal_year')) {
                $query->where('fiscal_year', $fiscalYear);
            }

            if ($rc = $request->get('responsibility_center')) {
                $query->where('responsibility_center', $rc);
            }

            if ($employeeType = $request->get('employee_type')) {
                $query->where('employee_type', $employeeType);
            }

            if ($obrStatus = $request->get('obr_status')) {
                $query->where('obr_status', $obrStatus);
            }

            if ($obrType = $request->get('obr_type')) {
                $query->where('obr_type', $obrType);
            }

            $perPage = (int) $request->get('per_page', 10);
            $paginated = $query->paginate($perPage);

            $total = EmployeeFundTransaction::count();
            $myCount = Auth::id()
                ? EmployeeFundTransaction::where('created_by', Auth::id())->count()
                : 0;

            return response()->json([
                'data'           => $paginated->items(),
                'total'          => $total,
                'filtered_total' => $paginated->total(),
                'per_page'       => $paginated->perPage(),
                'current_page'   => $paginated->currentPage(),
                'last_page'      => $paginated->lastPage(),
                'my_records_count' => $myCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching employee fund transactions', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Error fetching transactions',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new transaction.
     */
    public function store(StoreEmployeeFundTransactionRequest $request): JsonResponse
    {
        try {
            $record = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data'    => $record,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating employee fund transaction', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating transaction',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single transaction.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $record = EmployeeFundTransaction::with(['creator', 'updater', 'responsibilityCenter', 'employees.employeeRecord'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    /**
     * Update a transaction.
     */
    public function update(UpdateEmployeeFundTransactionRequest $request, int $id): JsonResponse
    {
        try {
            $record = EmployeeFundTransaction::findOrFail($id);
            $record = $this->service->update($record, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating employee fund transaction ' . $id, ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating transaction',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update only the status (and optional remarks) of a transaction.
     */
    public function updateStatus(UpdateEmployeeFundTransactionStatusRequest $request, int $id): JsonResponse
    {
        try {
            $record = EmployeeFundTransaction::findOrFail($id);
            $record = $this->service->updateStatus($record, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating status for employee fund transaction ' . $id, ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating status',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft-delete a transaction (admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPermission('employee_fund_transactions.delete')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete transactions',
            ], 403);
        }

        try {
            $record = EmployeeFundTransaction::findOrFail($id);
            $this->service->delete($record);

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting employee fund transaction ' . $id, ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting transaction',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate DV (Disbursement Voucher) PDF for a transaction.
     */
    public function generateDVPdf(int $id): JsonResponse
    {
        $record = EmployeeFundTransaction::with(['creator', 'responsibilityCenter'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $record,
        ]);
    }

    /**
     * Generate OBR (Obligation Request) PDF for a transaction.
     */
    public function generateOBRPdf(int $id): JsonResponse
    {
        $record = EmployeeFundTransaction::with(['creator', 'responsibilityCenter'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $record,
        ]);
    }

    /**
     * Generate Payroll PDF for a transaction.
     */
    public function generatePayrollPdf(int $id): JsonResponse
    {
        $record = EmployeeFundTransaction::with(['creator', 'responsibilityCenter'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $record,
        ]);
    }

    /**
     * Update OBR tracking info (fiscal_year, obr_no, date_obligated, dv_no, obr_status).
     */
    public function updateObr(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year'    => 'nullable|integer|min:2000|max:2100',
            'obr_no'         => 'nullable|string|max:100',
            'date_obligated' => 'nullable|date',
            'dv_no'          => 'nullable|string|max:100',
            'obr_status'     => 'nullable|string|in:No OBR,LOA,Irregular,Transferred,Claimed,Paid,On Process,Denied',
        ]);

        try {
            $record = EmployeeFundTransaction::findOrFail($id);
            $record->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'OBR info updated successfully',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating OBR info for employee fund transaction ' . $id, ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating OBR info',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
