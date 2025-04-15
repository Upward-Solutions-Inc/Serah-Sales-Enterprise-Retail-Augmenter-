<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ReportsController;

Route::get('/reports', [ReportsController::class, 'index'])->name('payroll.reports');
Route::get('/reports/data', [ReportsController::class, 'data'])->name('payroll.reports.data');
Route::get('/reports/users', [ReportsController::class, 'getUsers'])->name('payroll.reports.users');
Route::post('/reports/generate', [ReportsController::class, 'generate'])->name('payroll.reports.generate');
Route::post('/reports/delete', [ReportsController::class, 'delete'])->name('payroll.reports.delete');