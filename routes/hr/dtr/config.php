<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\ConfigurationController;

Route::middleware(['auth'])->prefix('config')->group(function () {
    Route::get('/configuration', [ConfigurationController::class, 'index'])->name('config.configuration');
    Route::post('/configuration/store', [ConfigurationController::class, 'store'])->name('config.config.store');
});