<?php

namespace App\Services\Hr\Payroll;

use App\Models\Hr\Payroll\PayrollSalary;
use App\Models\Hr\Payroll\PayrollComponent;
use App\Models\Hr\Dtr\DtrLog;
use App\Models\Hr\Payroll\PayrollPayslip;
use App\Models\Hr\Payroll\PayrollCount;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected $salary;
    protected $components;
    protected $dtrLogs;

    public function __construct(PayrollSalary $salary = null, array $components = [], $dtrLogs = null)
    {
        $this->salary = $salary;
        $this->components = $components;
        $this->dtrLogs = $dtrLogs;
    }

    public function generatePayrollBatch(array $userIds, $start, $end, $payrollType, $createdBy): void
    {
        $logs = DtrLog::whereIn('user_id', $userIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $insertRows = [];
        $totals = [
            'gross' => 0,
            'net' => 0,
            'sss' => 0,
            'philhealth' => 0,
            'pagibig' => 0,
            'compensation' => 0
        ];

        $payrollCount = PayrollCount::create([
            'date_range_start' => $start,
            'date_range_end'   => $end,
            'payroll_type'     => $payrollType,
            'total_employees'  => count($userIds),
            'created_by'       => $createdBy,
        ]);

        foreach ($userIds as $userId) {
            $roleId = DB::table('role_user')->where('user_id', $userId)->value('role_id');
            if (!$roleId) continue;

            $salary = PayrollSalary::where('role_id', $roleId)->first();
            if (!$salary) continue;

            $dtrLogs = $logs->get($userId, collect());
            $payroll = new self($salary, $this->components, $dtrLogs);
            $result = $payroll->computePayroll(['earnings' => [], 'deductions' => []]);

            $insertRows[] = $this->buildPayslipRow($userId, $payrollCount->id, $result);
            $this->accumulateTotals($totals, $result, $salary->monthly_salary);
        }

        PayrollPayslip::insert($insertRows);

        $payrollCount->update([
            'total_sss'          => $totals['sss'],
            'total_philhealth'   => $totals['philhealth'],
            'total_pagibig'      => $totals['pagibig'],
            'total_gross'        => $totals['gross'],
            'total_net'          => $totals['net'],
            'total_compensation' => $totals['compensation'],
        ]);
    }

    protected function buildPayslipRow($userId, $countId, $result): array
    {
        return [
            'user_id'          => $userId,
            'payroll_count_id' => $countId,
            'allowance'        => 0,
            'sss'              => $result['sss'],
            'philhealth'       => $result['philhealth'],
            'pagibig'          => $result['pagibig'],
            'earnings'         => json_encode([]),
            'deductions'       => json_encode([]),
            'gross'            => $result['gross_pay'],
            'net'              => $result['net_pay'],
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }

    protected function accumulateTotals(&$totals, $result, $monthlySalary): void
    {
        $totals['sss'] += $result['sss'];
        $totals['philhealth'] += $result['philhealth'];
        $totals['pagibig'] += $result['pagibig'];
        $totals['gross'] += $result['gross_pay'];
        $totals['net'] += $result['net_pay'];
        $totals['compensation'] += $monthlySalary;
    }

    public function calculateGrossPay(array $earnings): float
    {
        return array_sum($earnings);
    }

    public function calculateDeductions(array $deductions): float
    {
        return array_sum($deductions);
    }

    public function calculateNetPay(float $gross, float $deductions): float
    {
        return $gross - $deductions;
    }

    public function calculateOvertimeFromDtrLogs(): float
    {
        $totalOvertimeMinutes = 0;
        if ($this->dtrLogs) {
            foreach ($this->dtrLogs as $log) {
                $totalOvertimeMinutes += $log->overtime_minutes;
            }
        }
        $overtimeHours = $totalOvertimeMinutes / 60;
        $hourlyRate = $this->salary->monthly_salary / (22 * 8);
        return $overtimeHours * $hourlyRate;
    }

    public function computePayroll(array $data): array
    {
        $monthlySalary = (float) $this->salary->monthly_salary;

        $sssRate        = isset($this->components['sss']) ? (float) $this->components['sss'] : 0.045;
        $philhealthRate = isset($this->components['philhealth']) ? (float) $this->components['philhealth'] : 0.025;
        $pagibigRate    = isset($this->components['pagibig']) ? (float) $this->components['pagibig'] : 0.02;

        $sss        = $monthlySalary * $sssRate;
        $philhealth = $monthlySalary * $philhealthRate;
        $pagibig    = $monthlySalary < 1500 ? 100 : $monthlySalary * $pagibigRate;

        $overtimeAmount = $this->calculateOvertimeFromDtrLogs();
        $grossEarnings = $this->calculateGrossPay($data['earnings'] ?? []) + $overtimeAmount;
        $totalDeductions = $this->calculateDeductions($data['deductions'] ?? []) + $sss + $philhealth + $pagibig;
        $netPay = $this->calculateNetPay($grossEarnings, $totalDeductions);

        return [
            'monthly_salary'  => number_format($monthlySalary, 2, '.', ''),
            'overtime_amount' => number_format($overtimeAmount, 2, '.', ''),
            'gross_pay'       => number_format($grossEarnings, 2, '.', ''),
            'sss'             => number_format($sss, 2, '.', ''),
            'philhealth'      => number_format($philhealth, 2, '.', ''),
            'pagibig'         => number_format($pagibig, 2, '.', ''),
            'total_deduction' => number_format($totalDeductions, 2, '.', ''),
            'net_pay'         => number_format($netPay, 2, '.', ''),
        ];
    }
}