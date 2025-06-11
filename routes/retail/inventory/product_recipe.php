<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductRecipeController;

Route::get('/product_recipe', [ProductRecipeController::class, 'index'])->name('retail.inventory.product_recipe');