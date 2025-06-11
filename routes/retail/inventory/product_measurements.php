<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductMeasurementsController;

Route::get('/product_measurements', [ProductMeasurementsController::class, 'index'])->name('retail.inventory.product_measurements');