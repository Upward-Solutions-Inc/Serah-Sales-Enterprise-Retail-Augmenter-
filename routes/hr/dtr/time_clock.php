<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\TimeClockController;

Route::get('/time-clock', [TimeClockController::class, 'index'])->name('dtr.time_clock');
Route::post('/clock-in', [TimeClockController::class, 'clockIn'])->name('dtr.clock_in');
Route::post('/clock-out', [TimeClockController::class, 'clockOut'])->name('dtr.clock_out');
Route::get('/status', [TimeClockController::class, 'clockStatus'])->name('dtr.status');
Route::get('/logs', [TimeClockController::class, 'getLogs'])->name('dtr.logs');