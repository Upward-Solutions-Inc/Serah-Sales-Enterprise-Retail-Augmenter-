<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hr\Payroll\PayrollPayslip;
use App\Models\Hr\Payroll\PayrollComponent;
use App\Models\Core\Setting\Setting;

class PayslipController extends Controller
{
    public function index()
    {
        return view('custom.payroll.payslip');
    }

    public function employeePayslips()
    {
        $user = auth()->user();

        $companySettings = Setting::whereIn('name', ['company_name', 'company_logo'])->pluck('value', 'name');
        $companyName = $companySettings['company_name'] ?? 'NA';
        $companyLogo = $companySettings['company_logo'] ?? null;

        $branchName = optional($user->branchOrWarehouse)->name ?? 'NA';
        $components = PayrollComponent::pluck('label', 'id');
    
        $payslips = PayrollPayslip::with('payrollCount')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($p) use ($user, $branchName, $components) {
                $earnings = collect(is_array($p->earnings) ? $p->earnings : json_decode($p->earnings, true))
                    ->filter(fn($e) => isset($e['amount']) && isset($e['component_id']))
                    ->map(fn($e) => [
                        'description' => $components[$e['component_id']] ?? 'Unknown',
                        'total' => $e['amount']
                    ])->toArray();

                $deductions = collect(is_array($p->deductions) ? $p->deductions : json_decode($p->deductions, true))
                    ->filter(fn($d) => isset($d['amount']) && isset($d['component_id']))
                    ->map(fn($d) => [
                        'description' => $components[$d['component_id']] ?? 'Unknown',
                        'total' => $d['amount']
                    ])->toArray();
                    
                $start = \Carbon\Carbon::parse($p->payrollCount->date_range_start);
                $end = \Carbon\Carbon::parse($p->payrollCount->date_range_end);
                $formatted_range = $start->format('F j') . '-' . $end->format('j, Y');
    
                return [
                    'date_range'        => $formatted_range,
                    'date_start'        => $start->toDateString(),
                    'date_end'          => $end->toDateString(),
                    'pay_date'          => $p->created_at ? $p->created_at->format('F j, Y') : 'NA',
                    'payroll_type'      => $p->payrollCount->payroll_type,
                    'branch'            => optional($user->branchOrWarehouse)->name ?? 'NA',
    
                    'basic_pay'         => (float) $p->basic_pay,
                    'overtime_pay'      => (float) $p->overtime_pay,
                    'allowance'         => (float) $p->allowance,
                    'night_diff'        => (float) $p->night_differential,

                    'night_diff'        => (float) $p->night_differential,
                    'income_tax'        => (float) $p->income_tax,
                    'sss'               => (float) $p->sss,
                    'pagibig'           => (float) $p->pagibig,
                    'philhealth'        => (float) $p->philhealth,
                    'total_allowance'   => collect($earnings)->sum(fn($e) => (float) $e['total']),
                    'earnings'          => $earnings,
                    'deductions'        => $deductions,
                    'gross'             => (float) $p->gross,
                    'net'               => (float) $p->net,
                ];
            });
    
        return response()->json([
            'company' => [
                'name' => $companyName,
                'logo' => $companyLogo,
            ],
            'payslips' => $payslips
        ]);
    }
}