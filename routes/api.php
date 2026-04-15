<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeFundTransactionController;
use App\Http\Controllers\Api\ResponsibilityCenterController;
use App\Http\Controllers\Api\SignatoryController;
use Illuminate\Support\Facades\Route;

// All API routes require auth via the web guard (shared session cookie)
Route::middleware(['web', 'auth'])->group(function () {
    // Employee Fund Transactions
    Route::get('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'index']);
    Route::post('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'store']);
    Route::get('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'show']);
    Route::put('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'update']);
    Route::patch('/employee-fund-transactions/{id}/update-status', [EmployeeFundTransactionController::class, 'updateStatus']);
    Route::patch('/employee-fund-transactions/{id}/update-obr', [EmployeeFundTransactionController::class, 'updateObr']);
    Route::delete('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'destroy']);
    Route::get('/employee-fund-transactions/{id}/dv-pdf', [EmployeeFundTransactionController::class, 'generateDVPdf']);
    Route::get('/employee-fund-transactions/{id}/obr-pdf', [EmployeeFundTransactionController::class, 'generateOBRPdf']);
    Route::get('/employee-fund-transactions/{id}/payroll-pdf', [EmployeeFundTransactionController::class, 'generatePayrollPdf']);

    // Responsibility Centers
    Route::get('/responsibility-centers', [ResponsibilityCenterController::class, 'index']);
    Route::post('/responsibility-centers', [ResponsibilityCenterController::class, 'store']);
    Route::put('/responsibility-centers/{id}', [ResponsibilityCenterController::class, 'update']);
    Route::delete('/responsibility-centers/{id}', [ResponsibilityCenterController::class, 'destroy']);
    Route::get('/responsibility-centers/{id}/particulars', [ResponsibilityCenterController::class, 'particulars']);
    Route::post('/responsibility-centers/{id}/particulars', [ResponsibilityCenterController::class, 'storeParticular']);
    Route::put('/responsibility-centers/{id}/particulars/{particulerId}', [ResponsibilityCenterController::class, 'updateParticular']);
    Route::delete('/responsibility-centers/{id}/particulars/{particulerId}', [ResponsibilityCenterController::class, 'destroyParticular']);

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::patch('/employees/{id}/toggle-active', [EmployeeController::class, 'toggleActive']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // Signatories
    Route::get('/signatories', [SignatoryController::class, 'index']);
    Route::post('/signatories', [SignatoryController::class, 'upsert']);
    Route::post('/signatories/office-heads', [SignatoryController::class, 'storeOfficeHead']);
    Route::patch('/signatories/office-heads/{id}', [SignatoryController::class, 'updateOfficeHead']);
    Route::delete('/signatories/office-heads/{id}', [SignatoryController::class, 'destroyOfficeHead']);
});
