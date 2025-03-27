<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Payroll\ComputationController;

Route::get('/computation', [ComputationController::class, 'view'])->name('payroll.computation');
Route::get('/computation/data', [ComputationController::class, 'data'])->name('payroll.computation.data');
Route::get('/computation/dynamicData', [ComputationController::class, 'getDynamicData'])->name('payroll.computation.dynamicData');
Route::post('/computation/updatePay', [ComputationController::class, 'updatePay'])->name('payroll.computation.updatePay');
Route::post('/computation/updateRate', [ComputationController::class, 'updateRate'])->name('payroll.computation.updateRate');
Route::post('/computation/updateCompenOrDeduc', [ComputationController::class, 'updateCompenOrDeduc'])->name('payroll.computation.updateCompenOrDeduc');