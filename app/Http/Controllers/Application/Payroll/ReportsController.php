<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Core\Auth\User;

class ReportsController extends Controller
{
    public function index()
    {
        return view('custom.payroll.reports');
    }

    public function getUsers()
    {
        $users = User::select('id', 'first_name', 'last_name')->get();
        return response()->json($users);
    }
}