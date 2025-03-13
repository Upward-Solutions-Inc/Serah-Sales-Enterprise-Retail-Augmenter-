<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ReportsController;

Route::middleware(['auth'])->prefix('payroll')->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('payroll.reports');
});