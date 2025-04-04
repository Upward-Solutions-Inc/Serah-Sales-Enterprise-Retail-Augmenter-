<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ReportsController;

Route::get('/reports', [ReportsController::class, 'index'])->name('payroll.reports');
Route::get('/reports/users', [ReportsController::class, 'getUsers'])->name('payroll.reports.users');
