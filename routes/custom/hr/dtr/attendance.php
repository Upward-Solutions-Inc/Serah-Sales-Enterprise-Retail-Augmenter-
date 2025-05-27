<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\AttendanceController;

Route::get('/attendance', [AttendanceController::class, 'index'])->name('dtr.attendance');
Route::get('/attendance/logs', [AttendanceController::class, 'getLogs'])->name('dtr.attendance.logs');