<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductRecipeController;

Route::get('/product_recipe', [ProductRecipeController::class, 'index'])->name('retail.inventory.product_recipe');
Route::get('/product_recipes/dropdowns', [ProductRecipeController::class, 'fetchDropdowns']);
Route::get('/product_recipes/ingredients', [ProductRecipeController::class, 'fetchIngredients']);
Route::get('/product_recipes/products', [ProductRecipeController::class, 'fetchProducts']);