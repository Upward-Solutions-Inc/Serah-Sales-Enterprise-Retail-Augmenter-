<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComputationController extends Controller
{
    public function view()
    {
        return view('custom.payroll.computation');
    }
}