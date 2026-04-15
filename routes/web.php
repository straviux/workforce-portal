<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// CSRF token endpoint (for refreshing stale tokens on SPAs)
Route::get('/csrf-token', fn() => response()->json(['token' => csrf_token()]));

// Auth
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Authenticated area
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/employee-fund-transactions', fn() => inertia('EmployeeFundTransactions/index'))
        ->middleware('check.permission:employee_fund_transactions.view')
        ->name('employee_fund_transactions.index');

    Route::get('/employees', fn() => inertia('Employees/index'))
        ->name('employees.index');

    Route::get('/responsibility-centers', fn() => inertia('ResponsibilityCenter/index'))
        ->name('responsibility_centers.index');

    Route::get('/settings/signatories', fn() => inertia('Settings/Signatories'))
        ->name('settings.signatories');
});
