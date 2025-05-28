<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\IngredientsController;

Route::get('/ingredients', [IngredientsController::class, 'index'])->name('retail.inventory.ingredients');