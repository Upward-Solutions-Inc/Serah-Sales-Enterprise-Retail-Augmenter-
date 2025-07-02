<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductRecipeController;

Route::get('/product_recipe', [ProductRecipeController::class, 'index'])->name('retail.inventory.product_recipe');
Route::get('/product_recipes/dropdowns', [ProductRecipeController::class, 'fetchDropdowns']);
Route::get('/product_recipes/ingredients', [ProductRecipeController::class, 'fetchIngredients']);
Route::get('/product_recipes/products', [ProductRecipeController::class, 'fetchProducts']);
Route::get('/product_recipes/list', [ProductRecipeController::class, 'fetchAllRecipes']);
Route::get('/product_recipes', [ProductRecipeController::class, 'fetchAllRecipes']);
Route::get('/product_recipes/{id}', [ProductRecipeController::class, 'show']);
Route::post('/product_recipes', [ProductRecipeController::class, 'store']);
Route::put('/product_recipes/{id}', [ProductRecipeController::class, 'update']);
Route::delete('/product_recipes/{id}', [ProductRecipeController::class, 'destroy']);