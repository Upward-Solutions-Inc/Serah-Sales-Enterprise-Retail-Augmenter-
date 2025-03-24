<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ComputationController;

Route::get('/computation', [ComputationController::class, 'view'])->name('payroll.computation');