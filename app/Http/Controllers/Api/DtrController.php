<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDtrReportRequest;
use App\Models\Employee;
use App\Services\DtrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DtrController extends Controller
{
    public function __construct(private readonly DtrService $service) {}

    public function personal(Request $request): JsonResponse
    {
        try {
            return response()->json($this->service->setupPayload($request->user()->fresh()));
        } catch (\Throwable $exception) {
            Log::error('Error loading personal DTR setup', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load personal DTR setup.'], 500);
        }
    }

    public function employees(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->get('search', ''));
            $perPage = max(1, min((int) $request->get('per_page', 12), 25));

            if ($search === '') {
                return response()->json([
                    'data' => [],
                    'filtered_total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'last_page' => 1,
                ]);
            }

            $query = Employee::query()
                ->where('employee_type', 'contract_of_service')
                ->withCount(['dtrReports as dtr_reports_count'])
                ->latest();

            $query->where(function ($employeeQuery) use ($search) {
                $employeeQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('office', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('agency', 'like', "%{$search}%");
            });

            $paginated = $query->paginate($perPage);

            return response()->json([
                'data' => collect($paginated->items())->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'office' => $employee->office,
                    'designation' => $employee->designation,
                    'employee_type' => $employee->employee_type,
                    'dtr_reports_count' => (int) $employee->dtr_reports_count,
                ])->values(),
                'filtered_total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error loading employee DTR subjects', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load employee DTR subjects.'], 500);
        }
    }

    public function employee(int $id): JsonResponse
    {
        try {
            return response()->json($this->service->setupPayload($this->employeeRecord($id)));
        } catch (\Throwable $exception) {
            Log::error('Error loading employee DTR setup', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load employee DTR setup.'], 500);
        }
    }

    public function storePersonalReport(StoreDtrReportRequest $request): JsonResponse
    {
        try {
            $report = $this->service->createReport($request->user(), 'personal', $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Personal DTR generated.',
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error generating personal DTR', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not generate personal DTR.'], 500);
        }
    }

    public function personalReport(Request $request, int $id): JsonResponse
    {
        try {
            $report = $this->personalReportRecord($request, $id);

            return response()->json([
                'data' => $this->service->reportDetail($report),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error loading personal DTR report', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load personal DTR report.'], 500);
        }
    }

    public function updatePersonalReport(StoreDtrReportRequest $request, int $id): JsonResponse
    {
        try {
            $report = $this->service->updateReport($this->personalReportRecord($request, $id), $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Personal DTR updated.',
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error updating personal DTR', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not update personal DTR.'], 500);
        }
    }

    public function deletePersonalReport(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->deleteReport($this->personalReportRecord($request, $id));

            return response()->json([
                'message' => 'Personal DTR deleted.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error deleting personal DTR', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not delete personal DTR.'], 500);
        }
    }

    public function storeEmployeeReport(StoreDtrReportRequest $request, int $id): JsonResponse
    {
        try {
            $report = $this->service->createReport($this->employeeRecord($id), 'employee', $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Employee DTR generated.',
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error generating employee DTR', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not generate employee DTR.'], 500);
        }
    }

    public function employeeReport(int $id, int $reportId): JsonResponse
    {
        try {
            $report = $this->employeeReportRecord($id, $reportId);

            return response()->json([
                'data' => $this->service->reportDetail($report),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error loading employee DTR report', [
                'id' => $id,
                'report_id' => $reportId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Could not load employee DTR report.'], 500);
        }
    }

    public function updateEmployeeReport(StoreDtrReportRequest $request, int $id, int $reportId): JsonResponse
    {
        try {
            $report = $this->service->updateReport($this->employeeReportRecord($id, $reportId), $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Employee DTR updated.',
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error updating employee DTR', [
                'id' => $id,
                'report_id' => $reportId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Could not update employee DTR.'], 500);
        }
    }

    public function deleteEmployeeReport(int $id, int $reportId): JsonResponse
    {
        try {
            $this->service->deleteReport($this->employeeReportRecord($id, $reportId));

            return response()->json([
                'message' => 'Employee DTR deleted.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error deleting employee DTR', [
                'id' => $id,
                'report_id' => $reportId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Could not delete employee DTR.'], 500);
        }
    }

    private function employeeRecord(int $id): Employee
    {
        return Employee::query()->findOrFail($id);
    }

    private function personalReportRecord(Request $request, int $id)
    {
        return $request->user()
            ->dtrReports()
            ->with(['dailyValues', 'generator'])
            ->findOrFail($id);
    }

    private function employeeReportRecord(int $id, int $reportId)
    {
        return $this->employeeRecord($id)
            ->dtrReports()
            ->with(['dailyValues', 'generator'])
            ->findOrFail($reportId);
    }
}
