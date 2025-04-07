<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Core\Auth\User;
use App\Services\Hr\Payroll\PayrollService;
use App\Models\Hr\Payroll\PayrollComponent;
use Illuminate\Support\Facades\DB;

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

    public function generate(Request $request)
    {
        $userIds     = $request->input('user_ids');
        $start       = $request->input('start_date');
        $end         = $request->input('end_date');
        $payrollType = $request->input('payroll_type');
        $createdBy   = auth()->id();

        $components = PayrollComponent::pluck('value', 'code')->toArray();

        DB::transaction(function () use ($userIds, $start, $end, $payrollType, $createdBy, $components) {
            $payrollService = new PayrollService(null, $components);
            $payrollService->generatePayrollBatch($userIds, $start, $end, $payrollType, $createdBy);
        });

        return response()->json(['success' => true]);
    }
}