<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\PayslipController;

Route::get('/payslip', [PayslipController::class, 'index'])->name('payroll.payslip');
Route::get('/payslip/employeePayslips', [PayslipController::class, 'employeePayslips'])->name('payroll.payslip.employeePayslips');