<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeIdController extends Controller
{

    public function index()
    {
        return view('custom.dtr.employee_id');
    }
}