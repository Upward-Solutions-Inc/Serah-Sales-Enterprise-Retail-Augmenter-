<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\TimeClockController;

Route::middleware(['auth'])->prefix('timeclock')->group(function () {
    Route::get('/time-clock', [TimeClockController::class, 'index'])->name('timeclock.time_clock');
    Route::post('/clock-in', [TimeClockController::class, 'clockIn'])->name('timeclock.clock_in');
    Route::post('/clock-out', [TimeClockController::class, 'clockOut'])->name('timeclock.clock_out');
    Route::get('/status', [TimeClockController::class, 'clockStatus'])->name('timeclock.status');
    Route::get('/logs', [TimeClockController::class, 'getLogs'])->name('timeclock.logs');

});