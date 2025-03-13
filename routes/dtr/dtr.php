<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\DtrController;
use App\Http\Controllers\Application\DTR\ConfigurationController;

Route::middleware(['auth'])->prefix('dtr')->group(function () {
    Route::get('/clock-in-out', [DtrController::class, 'index'])->name('dtr.clock_in_out');
    Route::post('/clock-in', [DtrController::class, 'clockIn'])->name('dtr.clock_in');
    Route::post('/clock-out', [DtrController::class, 'clockOut'])->name('dtr.clock_out');

    Route::get('/configuration', [ConfigurationController::class, 'index'])->name('dtr.configuration');
});