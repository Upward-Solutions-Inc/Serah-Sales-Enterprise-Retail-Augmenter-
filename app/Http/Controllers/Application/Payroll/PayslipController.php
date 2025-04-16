<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hr\Payroll\PayrollPayslip;

class PayslipController extends Controller
{
    public function index()
    {
        return view('custom.payroll.payslip');
    }

    public function employeePayslips()
    {
        $userId = auth()->id();
    
        $payslips = PayrollPayslip::with('payrollCount')
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($p) {
                $earnings = is_string($p->earnings) ? json_decode($p->earnings, true) : ($p->earnings ?? []);
                $deductions = is_string($p->deductions) ? json_decode($p->deductions, true) : ($p->deductions ?? []);
    
                return [
                    'date_range'       => $p->payrollCount->date_range_start . ' to ' . $p->payrollCount->date_range_end,
                    'payroll_type'     => $p->payrollCount->payroll_type,
                    'basic_pay'        => (float) $p->basic_pay,
                    'overtime_pay'     => (float) $p->overtime_pay,
                    'allowance'        => (float) $p->allowance + collect($earnings)->sum(fn($e) => (float) $e['amount']),
                    'income_tax'       => (float) $p->income_tax,
                    'sss'              => (float) $p->sss,
                    'pagibig'          => (float) $p->pagibig,
                    'philhealth'       => (float) $p->philhealth,
                    'gross'            => (float) $p->gross,
                    'net'              => (float) $p->net,
                    'deductions'       => $deductions,
                ];
            });
    
        return response()->json($payslips);
    }
}