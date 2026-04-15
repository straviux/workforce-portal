<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::with(['responsibilityCenter', 'creator'])->latest();

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('office', 'like', "%{$search}%");
                });
            }

            if ($type = $request->get('employee_type')) {
                $query->where('employee_type', $type);
            }

            if ($request->has('is_active') && $request->get('is_active') !== '') {
                $query->where('is_active', (bool) $request->get('is_active'));
            }

            $perPage = (int) $request->get('per_page', 15);
            $paginated = $query->paginate($perPage);

            return response()->json([
                'data'           => $paginated->items(),
                'total'          => Employee::count(),
                'filtered_total' => $paginated->total(),
                'per_page'       => $paginated->perPage(),
                'current_page'   => $paginated->currentPage(),
                'last_page'      => $paginated->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching employees', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching employees'], 500);
        }
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $employee = $this->service->create($request->validated());
            $employee->load(['responsibilityCenter', 'creator']);
            return response()->json(['data' => $employee, 'message' => 'Employee created.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating employee', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating employee'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $employee = Employee::with(['responsibilityCenter', 'creator', 'updater'])->findOrFail($id);
        return response()->json(['data' => $employee]);
    }

    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $updated = $this->service->update($employee, $request->validated());
            $updated->load(['responsibilityCenter', 'creator']);
            return response()->json(['data' => $updated, 'message' => 'Employee updated.']);
        } catch (\Exception $e) {
            Log::error('Error updating employee', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating employee'], 500);
        }
    }

    public function toggleActive(int $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $updated = $this->service->toggleActive($employee);
            return response()->json(['data' => $updated, 'message' => $updated->is_active ? 'Employee activated.' : 'Employee deactivated.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating status'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $this->service->delete($employee);
            return response()->json(['message' => 'Employee deleted.']);
        } catch (\Exception $e) {
            Log::error('Error deleting employee', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting employee'], 500);
        }
    }
}
