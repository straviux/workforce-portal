<?php

use App\Http\Controllers\Api\EmployeeFundTransactionController;
use App\Http\Controllers\Api\ResponsibilityCenterController;
use Illuminate\Support\Facades\Route;

// All API routes require auth via the web guard (shared session cookie)
Route::middleware(['web', 'auth'])->group(function () {
    // Employee Fund Transactions
    Route::get('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'index']);
    Route::post('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'store']);
    Route::get('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'show']);
    Route::put('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'update']);
    Route::patch('/employee-fund-transactions/{id}/update-status', [EmployeeFundTransactionController::class, 'updateStatus']);
    Route::delete('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'destroy']);
    Route::get('/employee-fund-transactions/{id}/dv-pdf', [EmployeeFundTransactionController::class, 'generateDVPdf']);
    Route::get('/employee-fund-transactions/{id}/obr-pdf', [EmployeeFundTransactionController::class, 'generateOBRPdf']);
    Route::get('/employee-fund-transactions/{id}/payroll-pdf', [EmployeeFundTransactionController::class, 'generatePayrollPdf']);

    // Responsibility Centers
    Route::get('/responsibility-centers', [ResponsibilityCenterController::class, 'index']);
    Route::get('/responsibility-centers/{id}/particulars', [ResponsibilityCenterController::class, 'particulars']);
});
