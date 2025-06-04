<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\Retail\Inventory\ProductInventoryController;

Route::get('/product_inventory', [ProductInventoryController::class, 'index'])->name('retail.inventory.product_inventory');