<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\PayslipController;

Route::middleware(['auth'])->prefix('payslip')->group(function () {
    Route::get('/payslip', [PayslipController::class, 'index'])->name('payslip.payslip');
});