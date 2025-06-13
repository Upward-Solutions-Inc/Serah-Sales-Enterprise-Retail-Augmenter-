<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductIngredientsController;

Route::get('/product_ingredients', [ProductIngredientsController::class, 'index'])->name('retail.inventory.product_ingredients');
Route::get('/product_ingredients/measurements/fetch', [ProductIngredientsController::class, 'fetchMeasurements'])->name('retail.inventory.product_ingredients.measurements.fetch');
Route::get('/product_ingredients/list', [ProductIngredientsController::class, 'fetchIngredients'])->name('retail.inventory.product_ingredients.list');
Route::post('/product_ingredients/store', [ProductIngredientsController::class, 'store'])->name('retail.inventory.product_ingredients.store');
Route::get('/product_ingredients/{id}', [ProductIngredientsController::class, 'show']);
Route::put('/product_ingredients/{id}', [ProductIngredientsController::class, 'update']);
Route::delete('/product_ingredients/{id}', [ProductIngredientsController::class, 'destroy']);