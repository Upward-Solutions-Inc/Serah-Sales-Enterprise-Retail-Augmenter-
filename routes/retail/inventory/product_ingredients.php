<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductIngredientsController;

Route::get('/product_ingredients', [ProductIngredientsController::class, 'index'])->name('retail.inventory.product_ingredients');