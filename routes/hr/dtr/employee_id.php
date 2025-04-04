<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\DTR\EmployeeIdController;

Route::get('/employee_id', [EmployeeIdController::class, 'index'])->name('dtr.employee_id');