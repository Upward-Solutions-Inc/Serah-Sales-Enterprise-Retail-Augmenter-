<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use App\Events\PayrollGenerated;
use Illuminate\Http\Request;
use App\Models\Core\Auth\User;
use App\Services\Hr\Payroll\PayrollService;
use App\Models\Hr\Payroll\PayrollCount;
use App\Models\Hr\Payroll\PayrollComponent;
use App\Models\Hr\Payroll\PayrollPayslip;
use App\Models\Core\Setting\Setting;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('custom.payroll.reports');
    }

    public function getUsers()
    {
        $users = User::select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'roles.name as role', 'branch_or_warehouses.name as branch')
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
            ->leftJoin('branch_or_warehouses', 'users.branch_or_warehouse_id', '=', 'branch_or_warehouses.id')
            ->get();
    
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
                'total_night_differential' => $item->total_night_differential,
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

            PayrollCount::where('date_range_start', $start)
                ->where('date_range_end', $end)
                ->where('payroll_type', $payrollType)
                ->get()
                ->each(function ($existing) use ($userIds) {
                    $existingUserIds = PayrollPayslip::where('payroll_count_id', $existing->id)
                        ->pluck('user_id')
                        ->toArray();

                    sort($existingUserIds);
                    $submittedUserIds = $userIds;
                    sort($submittedUserIds);

                    if ($existingUserIds === $submittedUserIds) {
                        PayrollPayslip::where('payroll_count_id', $existing->id)->delete();
                        $existing->delete();
                    }
                });

            $payrollService = new PayrollService(null, $rates);
            $payrollService->generatePayrollBatch($userIds, $start, $end, $payrollType, $createdBy, $earnings, $deductions);
        });

        broadcast(new PayrollGenerated('payroll_ready'))->toOthers();
        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        DB::transaction(function () use ($id) {
            DB::table('payroll_payslips')->where('payroll_count_id', $id)->delete();
            DB::table('payroll_counts')->where('id', $id)->delete();
        });

        return response()->json(['success' => true]);
    }

    public function viewPayslipsJson($countId)
    {
        $companySettings = Setting::whereIn('name', ['company_name', 'company_logo'])->pluck('value', 'name');
        $companyName = $companySettings['company_name'] ?? 'NA';
        $companyLogo = $companySettings['company_logo'] ?? null;
    
        $components = PayrollComponent::pluck('label', 'id');
    
        $payslips = PayrollPayslip::with(['payrollCount', 'user.branchOrWarehouse'])
            ->where('payroll_count_id', $countId)
            ->get()
            ->map(function ($p) use ($components) {
                $user = $p->user;
                $branchName = optional($user->branchOrWarehouse)->name ?? 'NA';
    
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
                    'employee_name'      => $user->first_name . ' ' . $user->last_name,
                    'role'               => optional($user->roles->first())->name ?? 'NA',
                    'branch'             => $branchName,
                    'date_range'         => $formatted_range,
                    'date_start'         => $start->toDateString(),
                    'date_end'           => $end->toDateString(),
                    'pay_date'           => $p->created_at ? $p->created_at->format('F j, Y') : 'NA',
                    'payroll_type'       => $p->payrollCount->payroll_type,
    
                    'basic_pay'          => (float) $p->basic_pay,
                    'overtime_pay'       => (float) $p->overtime_pay,
                    'allowance'          => (float) $p->allowance,
                    'night_diff'         => (float) $p->night_differential,
                    'income_tax'         => (float) $p->income_tax,
                    'sss'                => (float) $p->sss,
                    'pagibig'            => (float) $p->pagibig,
                    'philhealth'         => (float) $p->philhealth,
                    'total_allowance'    => collect($earnings)->sum(fn($e) => (float) $e['total']),
                    'earnings'           => $earnings,
                    'deductions'         => $deductions,
                    'gross'              => (float) $p->gross,
                    'net'                => (float) $p->net,
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