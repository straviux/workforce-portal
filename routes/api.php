<?php

use App\Http\Controllers\Api\CertificationController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeFundTransactionController;
use App\Http\Controllers\Api\ResponsibilityCenterController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SignatoryController;
use App\Http\Controllers\Api\SwaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// All API routes require auth via the web guard (shared session cookie)
Route::middleware(['web', 'auth'])->group(function () {
    // Employee Fund Transactions
    Route::get('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'index']);
    Route::post('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'store']);
    Route::get('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'show']);
    Route::put('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'update']);
    Route::patch('/employee-fund-transactions/{id}/update-status', [EmployeeFundTransactionController::class, 'updateStatus']);
    Route::delete('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'destroy'])
        ->middleware('check.permission:employee_fund_transactions.delete');
    Route::get('/employee-fund-transactions/{id}/dv-pdf', [EmployeeFundTransactionController::class, 'generateDVPdf']);
    Route::get('/employee-fund-transactions/{id}/obr-pdf', [EmployeeFundTransactionController::class, 'generateOBRPdf']);
    Route::get('/employee-fund-transactions/{id}/payroll-pdf', [EmployeeFundTransactionController::class, 'generatePayrollPdf']);
    Route::get('/obr-tracking-info', [EmployeeFundTransactionController::class, 'getObrTrackingInfo']);

    // Responsibility Centers
    Route::get('/responsibility-centers', [ResponsibilityCenterController::class, 'index'])
        ->middleware('check.permission:responsibility_centers.view');
    Route::post('/responsibility-centers', [ResponsibilityCenterController::class, 'store'])
        ->middleware('check.permission:responsibility_centers.manage');
    Route::put('/responsibility-centers/{id}', [ResponsibilityCenterController::class, 'update'])
        ->middleware('check.permission:responsibility_centers.manage');
    Route::delete('/responsibility-centers/{id}', [ResponsibilityCenterController::class, 'destroy'])
        ->middleware('check.permission:responsibility_centers.delete');
    Route::get('/responsibility-centers/{id}/particulars', [ResponsibilityCenterController::class, 'particulars'])
        ->middleware('check.permission:responsibility_centers.view');
    Route::post('/responsibility-centers/{id}/particulars', [ResponsibilityCenterController::class, 'storeParticular'])
        ->middleware('check.permission:responsibility_centers.manage');
    Route::put('/responsibility-centers/{id}/particulars/{particulerId}', [ResponsibilityCenterController::class, 'updateParticular'])
        ->middleware('check.permission:responsibility_centers.manage');
    Route::delete('/responsibility-centers/{id}/particulars/{particulerId}', [ResponsibilityCenterController::class, 'destroyParticular'])
        ->middleware('check.permission:responsibility_centers.delete');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])
        ->middleware('check.permission:employees.view');
    Route::post('/employees', [EmployeeController::class, 'store'])
        ->middleware('check.permission:employees.manage');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])
        ->middleware('check.permission:employees.view');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])
        ->middleware('check.permission:employees.manage');
    Route::patch('/employees/{id}/toggle-active', [EmployeeController::class, 'toggleActive'])
        ->middleware('check.permission:employees.manage');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])
        ->middleware('check.permission:employees.delete');

    // Certifications
    Route::get('/certifications/non-ros', [CertificationController::class, 'nonRosIndex'])
        ->middleware('check.permission:certifications.view');
    Route::post('/certifications/non-ros', [CertificationController::class, 'storeNonRos'])
        ->middleware('check.permission:certifications.manage');
    Route::put('/certifications/non-ros/{id}', [CertificationController::class, 'updateNonRos'])
        ->middleware('check.permission:certifications.manage');
    Route::delete('/certifications/non-ros/{id}', [CertificationController::class, 'destroyNonRos'])
        ->middleware('check.permission:certifications.manage');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])
        ->middleware('check.permission:calendar.view');
    Route::post('/calendar', [CalendarController::class, 'store'])
        ->middleware('check.permission:calendar.manage');
    Route::put('/calendar/{id}', [CalendarController::class, 'update'])
        ->middleware('check.permission:calendar.manage');
    Route::delete('/calendar/{id}', [CalendarController::class, 'destroy'])
        ->middleware('check.permission:calendar.manage');

    // SWA
    Route::get('/swa/personal', [SwaController::class, 'personal'])
        ->middleware('check.permission:swa.view');
    Route::put('/swa/personal/tasks', [SwaController::class, 'syncPersonalTasks'])
        ->middleware('check.permission:swa.manage');
    Route::post('/swa/personal/reports', [SwaController::class, 'storePersonalReport'])
        ->middleware('check.permission:swa.manage');
    Route::get('/swa/personal/reports/{id}', [SwaController::class, 'personalReport'])
        ->middleware('check.permission:swa.view');
    Route::put('/swa/personal/reports/{id}', [SwaController::class, 'updatePersonalReport'])
        ->middleware('check.permission:swa.manage');
    Route::delete('/swa/personal/reports/{id}', [SwaController::class, 'deletePersonalReport'])
        ->middleware('check.permission:swa.manage');
    Route::get('/swa/employees', [SwaController::class, 'employees'])
        ->middleware('check.permission:swa.view');
    Route::get('/swa/employees/{id}', [SwaController::class, 'employee'])
        ->middleware('check.permission:swa.view');
    Route::put('/swa/employees/{id}/tasks', [SwaController::class, 'syncEmployeeTasks'])
        ->middleware('check.permission:swa.manage');
    Route::post('/swa/employees/{id}/reports', [SwaController::class, 'storeEmployeeReport'])
        ->middleware('check.permission:swa.manage');
    Route::get('/swa/employees/{id}/reports/{reportId}', [SwaController::class, 'employeeReport'])
        ->middleware('check.permission:swa.view');
    Route::put('/swa/employees/{id}/reports/{reportId}', [SwaController::class, 'updateEmployeeReport'])
        ->middleware('check.permission:swa.manage');
    Route::delete('/swa/employees/{id}/reports/{reportId}', [SwaController::class, 'deleteEmployeeReport'])
        ->middleware('check.permission:swa.manage');

    // Signatories
    Route::get('/signatories', [SignatoryController::class, 'index'])
        ->middleware('check.permission:signatories.view');
    Route::post('/signatories', [SignatoryController::class, 'upsert'])
        ->middleware('check.permission:signatories.manage');
    Route::post('/signatories/office-heads', [SignatoryController::class, 'storeOfficeHead'])
        ->middleware('check.permission:signatories.manage');
    Route::patch('/signatories/office-heads/{id}', [SignatoryController::class, 'updateOfficeHead'])
        ->middleware('check.permission:signatories.manage');
    Route::delete('/signatories/office-heads/{id}', [SignatoryController::class, 'destroyOfficeHead'])
        ->middleware('check.permission:signatories.manage');

    // Users
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('check.permission:users.view');
    Route::post('/users/import-scholarship', [UserController::class, 'importScholarship'])
        ->middleware('check.permission:users.manage');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('check.permission:users.manage');
    Route::put('/users/{id}', [UserController::class, 'update'])
        ->middleware('check.permission:users.manage');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])
        ->middleware('check.permission:users.manage');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('check.permission:roles.view');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('check.permission:roles.manage');
    Route::put('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('check.permission:roles.manage');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
        ->middleware('check.permission:roles.manage');
});
