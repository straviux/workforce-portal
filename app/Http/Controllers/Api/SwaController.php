<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSwaReportRequest;
use App\Http\Requests\SyncSwaTasksRequest;
use App\Models\Employee;
use App\Services\SwaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SwaController extends Controller
{
    public function __construct(private readonly SwaService $service) {}

    public function personal(Request $request): JsonResponse
    {
        try {
            return response()->json($this->service->setupPayload($request->user()->fresh()));
        } catch (\Throwable $exception) {
            Log::error('Error loading personal SWA setup', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load personal SWA setup.'], 500);
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
                ->withCount([
                    'swaTasks as active_swa_tasks_count' => fn($taskQuery) => $taskQuery->where('is_active', true),
                    'swaReports as swa_reports_count',
                ])
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
                'data' => collect($paginated->items())->map(fn(Employee $employee) => [
                    'id' => $employee->id,
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'office' => $employee->office,
                    'designation' => $employee->designation,
                    'employee_type' => $employee->employee_type,
                    'active_swa_tasks_count' => (int) $employee->active_swa_tasks_count,
                    'swa_reports_count' => (int) $employee->swa_reports_count,
                ])->values(),
                'filtered_total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error loading employee SWA subjects', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load employee SWA subjects.'], 500);
        }
    }

    public function employee(int $id): JsonResponse
    {
        try {
            return response()->json($this->service->setupPayload($this->employeeRecord($id)));
        } catch (\Throwable $exception) {
            Log::error('Error loading employee SWA setup', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load employee SWA setup.'], 500);
        }
    }

    public function syncPersonalTasks(SyncSwaTasksRequest $request): JsonResponse
    {
        try {
            $tasks = $this->service->syncTasks($request->user(), $request->validated('tasks'));

            return response()->json([
                'tasks' => $tasks,
                'message' => 'Personal SWA tasks saved.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error saving personal SWA tasks', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not save personal SWA tasks.'], 500);
        }
    }

    public function syncEmployeeTasks(SyncSwaTasksRequest $request, int $id): JsonResponse
    {
        try {
            $tasks = $this->service->syncTasks($this->employeeRecord($id), $request->validated('tasks'));

            return response()->json([
                'tasks' => $tasks,
                'message' => 'Employee SWA tasks saved.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error saving employee SWA tasks', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not save employee SWA tasks.'], 500);
        }
    }

    public function storePersonalReport(StoreSwaReportRequest $request): JsonResponse
    {
        try {
            $report = $this->service->createReport($request->user(), 'personal', $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Personal SWA generated.',
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error generating personal SWA', ['error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not generate personal SWA.'], 500);
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
            Log::error('Error loading personal SWA report', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not load personal SWA report.'], 500);
        }
    }

    public function updatePersonalReport(StoreSwaReportRequest $request, int $id): JsonResponse
    {
        try {
            $report = $this->service->updateReport($this->personalReportRecord($request, $id), $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Personal SWA updated.',
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error updating personal SWA', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not update personal SWA.'], 500);
        }
    }

    public function deletePersonalReport(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->deleteReport($this->personalReportRecord($request, $id));

            return response()->json([
                'message' => 'Personal SWA deleted.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error deleting personal SWA', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not delete personal SWA.'], 500);
        }
    }

    public function storeEmployeeReport(StoreSwaReportRequest $request, int $id): JsonResponse
    {
        try {
            $report = $this->service->createReport($this->employeeRecord($id), 'employee', $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Employee SWA generated.',
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error generating employee SWA', ['id' => $id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Could not generate employee SWA.'], 500);
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
            Log::error('Error loading employee SWA report', [
                'id' => $id,
                'report_id' => $reportId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Could not load employee SWA report.'], 500);
        }
    }

    public function updateEmployeeReport(StoreSwaReportRequest $request, int $id, int $reportId): JsonResponse
    {
        try {
            $report = $this->service->updateReport($this->employeeReportRecord($id, $reportId), $request->validated());

            return response()->json([
                'data' => $this->service->reportSummary($report),
                'message' => 'Employee SWA updated.',
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Error updating employee SWA', [
                'id' => $id,
                'report_id' => $reportId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Could not update employee SWA.'], 500);
        }
    }

    public function deleteEmployeeReport(int $id, int $reportId): JsonResponse
    {
        try {
            $this->service->deleteReport($this->employeeReportRecord($id, $reportId));

            return response()->json([
                'message' => 'Employee SWA deleted.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error deleting employee SWA', [
                'id' => $id,
                'report_id' => $reportId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Could not delete employee SWA.'], 500);
        }
    }

    private function employeeRecord(int $id): Employee
    {
        return Employee::query()->findOrFail($id);
    }

    private function personalReportRecord(Request $request, int $id)
    {
        return $request->user()
            ->swaReports()
            ->with(['tasks.dailyValues', 'generator'])
            ->findOrFail($id);
    }

    private function employeeReportRecord(int $id, int $reportId)
    {
        return $this->employeeRecord($id)
            ->swaReports()
            ->with(['tasks.dailyValues', 'generator'])
            ->findOrFail($reportId);
    }
}
