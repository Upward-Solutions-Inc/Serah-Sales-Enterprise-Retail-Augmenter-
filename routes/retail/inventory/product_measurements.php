<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductMeasurementsController;

Route::get('/product_measurements', [ProductMeasurementsController::class, 'index'])->name('retail.inventory.product_measurements');
Route::get('/product_measurements/list', [ProductMeasurementsController::class, 'fetchList']);
Route::post('/product_measurements/store', [ProductMeasurementsController::class, 'store']);
Route::get('/product_measurements/{id}', [ProductMeasurementsController::class, 'show']);
Route::put('/product_measurements/{id}', [ProductMeasurementsController::class, 'update']);
Route::delete('/product_measurements/{id}', [ProductMeasurementsController::class, 'destroy']);