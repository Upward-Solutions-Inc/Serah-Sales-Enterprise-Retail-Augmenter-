<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ComputationController;

Route::get('/computation', [ComputationController::class, 'view'])->name('payroll.computation');
Route::get('/computation/data', [ComputationController::class, 'data'])->name('payroll.computation.data');
