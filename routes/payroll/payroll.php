<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ReportsController;
use App\Http\Controllers\Application\Payroll\PayslipController;

Route::middleware(['auth'])->prefix('payroll')->group(function () {
    Route::get('/payslip', [PayslipController::class, 'index'])->name('payroll.payslip');
    Route::get('/reports', [ReportsController::class, 'index'])->name('payroll.reports');
});