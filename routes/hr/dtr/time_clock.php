<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\TimeClockController;

Route::get('/time-clock', [TimeClockController::class, 'index'])->name('dtr.time_clock');
Route::get('/status', [TimeClockController::class, 'clockStatus'])->name('dtr.status');
Route::get('/logs', [TimeClockController::class, 'getLogs'])->name('dtr.logs');

Route::post('/clock', [TimeClockController::class, 'clock'])->name('dtr.clock');
Route::post('/clock-in', [TimeClockController::class, 'clock'])->name('dtr.clock_in');
Route::post('/clock-out', [TimeClockController::class, 'clockOut'])->name('dtr.clock_out');
Route::post('/time_clock/uploadFace', [TimeClockController::class, 'uploadFace'])->name('dtr.time_clock.uploadFace');