<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\ConfigurationController;

Route::get('/configuration', [ConfigurationController::class, 'index'])->name('dtr.configuration');
Route::get('/configuration/data', [ConfigurationController::class, 'getConfig'])->name('dtr.configuration.data');
Route::post('/configuration/store', [ConfigurationController::class, 'store'])->name('dtr.store');