<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeService
{
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::create($data);
            Log::info('employee_created', ['id' => $employee->id, 'name' => $employee->full_name]);
            return $employee;
        });
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        $employee->refresh();
        return $employee;
    }

    public function toggleActive(Employee $employee): Employee
    {
        $employee->update(['is_active' => !$employee->is_active]);
        $employee->refresh();
        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
        Log::info('employee_deleted', ['id' => $employee->id]);
    }
}
