<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use App\Events\PayrollGenerated;
use Illuminate\Http\Request;
use App\Models\Core\Auth\User;
use App\Services\Hr\Payroll\PayrollService;
use App\Models\Hr\Payroll\PayrollCount;
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

    public function data()
    {
        $items = PayrollCount::with(['creator', 'payslips'])
            ->orderBy('created_at', 'desc')
            ->get();
    
        if ($items->isEmpty()) {
            return response()->json([]);
        }
    
        $reports = $items->map(function ($item) {
            return [
                'date_range'           => $item->date_range_start . ' to ' . $item->date_range_end,
                'payroll_type'         => $item->payroll_type,
                'total_employees'      => $item->total_employees,
                'total_basic_pay'      => $item->total_basic_pay,
                'total_allowance'      => $item->total_allowance,
                'total_overtime'       => $item->total_overtime_pay,
                'total_other_earnings' => $item->payslips->sum(fn($p) => collect($p->earnings ?? [])->sum('amount')),
                'total_other_deductions' => $item->payslips->sum(fn($p) => collect($p->deductions ?? [])->sum('amount')),
                'total_sss'            => $item->total_sss,
                'total_philhealth'     => $item->total_philhealth,
                'total_pagibig'        => $item->total_pagibig,
                'total_income_tax'     => $item->payslips->sum('income_tax'),
                'total_gross'          => $item->total_gross,
                'total_deductions'     => $item->payslips->sum(fn($p) =>
                    $p->sss + $p->philhealth + $p->pagibig + $p->income_tax + collect($p->deductions ?? [])->sum('amount')
                ),
                'total_net'            => $item->total_net,
                'generated_by'         => $item->creator->name ?? 'N/A',
                'id'                   => $item->id,
            ];
        });
    
        return response()->json($reports);
    }
    

    public function generate(Request $request)
    {
        $userIds     = $request->input('user_ids');
        $start       = $request->input('start_date');
        $end         = $request->input('end_date');
        $payrollType = $request->input('payroll_type');
        $createdBy   = auth()->id();

        $rates = PayrollComponent::where('group', 'rates')->pluck('value', 'code')->toArray();
        $earnings = PayrollComponent::where('group', 'earnings')->get()->map(fn($e) => [
            'component_id' => $e->id,
            'amount' => (float) $e->value
        ])->toArray();

        $deductions = PayrollComponent::where('group', 'deductions')->get()->map(fn($d) => [
            'component_id' => $d->id,
            'amount' => (float) $d->value
        ])->toArray();

        DB::transaction(function () use ($userIds, $start, $end, $payrollType, $createdBy, $rates, $earnings, $deductions) {
            $payrollService = new PayrollService(null, $rates);
            $payrollService->generatePayrollBatch($userIds, $start, $end, $payrollType, $createdBy, $earnings, $deductions);
        });

        broadcast(new PayrollGenerated('payroll_ready'))->toOthers();
        return response()->json(['success' => true]);
    }

}