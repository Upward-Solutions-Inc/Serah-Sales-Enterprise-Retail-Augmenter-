<?php

namespace App\Services\Hr\Payroll;

use App\Events\PayslipGenerated;
use App\Models\Hr\Payroll\PayrollSalary;
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

    public function generatePayrollBatch(array $userIds, $start, $end, $payrollType, $createdBy, array $earnings, array $deductions): void
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
            'compensation' => 0,
            'basic_pay' => 0,
            'overtime_pay' => 0,
            'night_differential' => 0,
            'allowance' => 0,
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
            $result = $payroll->computePayroll([
                'earnings' => $earnings,
                'deductions' => $deductions,
            ]);
    
            $insertRows[] = $this->buildPayslipRow($userId, $payrollCount->id, $result);
            $this->accumulateTotals($totals, $result, $result['monthly_salary']);
        }
    
        PayrollPayslip::insert($insertRows);
    
        $payrollCount->update([
            'total_sss'                => $totals['sss'],
            'total_philhealth'         => $totals['philhealth'],
            'total_pagibig'            => $totals['pagibig'],
            'total_gross'              => $totals['gross'],
            'total_net'                => $totals['net'],
            'total_compensation'       => $totals['compensation'],
            'total_basic_pay'          => $totals['basic_pay'],
            'total_overtime_pay'       => $totals['overtime_pay'],
            'total_night_differential' => $totals['night_differential'],
            'total_allowance'          => $totals['allowance'],
        ]);

        foreach ($userIds as $userId) {
            broadcast(new PayslipGenerated($userId, 'payslip_ready'))->toOthers();
        }
    }    

    protected function buildPayslipRow($userId, $countId, $result): array
    {
        return [
            'user_id'             => $userId,
            'payroll_count_id'    => $countId,
            'basic_pay'           => $result['basic_pay'],
            'overtime_pay'        => $result['overtime_amount'],
            'night_differential'  => $result['night_differential'],
            'allowance'           => 0,
            'income_tax'          => $result['income_tax'] ?? 0,
            'sss'                 => $result['sss'],
            'philhealth'          => $result['philhealth'],
            'pagibig'             => $result['pagibig'],
            'earnings'            => json_encode($result['raw_earnings'] ?? []),
            'deductions'          => json_encode($result['raw_deductions'] ?? []),
            'gross'               => $result['gross_pay'],
            'net'                 => $result['net_pay'],
            'created_at'          => now(),
            'updated_at'          => now(),
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
        $totals['basic_pay'] += $result['basic_pay'];
        $totals['overtime_pay'] += $result['overtime_amount'];
        $totals['night_differential'] += $result['night_differential'];
        $totals['allowance'] += 0;
    }

    public function calculateGrossPay(array $earnings): float
    {
        return array_sum(array_column($earnings, 'amount'));
    }
    
    public function calculateDeductions(array $deductions): float
    {
        return array_sum(array_column($deductions, 'amount'));
    }

    public function calculateNetPay(float $gross, float $deductions): float
    {
        return $gross - $deductions;
    }

    public function calculateBasicPayFromDtrLogs(): float
    {
        $pay = 0;
        $hourlyRate = round($this->salary->monthly_salary / (22 * 8), 2);
    
        foreach ($this->dtrLogs ?? [] as $log) {
            $workHours = (float) ($log->total_work_hours ?? 0);
            $overtime = ((int) ($log->overtime_minutes ?? 0)) / 60;
            $graceLate = ((int) ($log->grace_late_minutes ?? 0)) / 60;

            $regularHours = max(0, $workHours - $overtime) + $graceLate;
            $pay += $regularHours * $hourlyRate; // include day + night regular hours
        }
    
        return $pay;
    }     

    public function calculateNightDiffFromDtrLogs(): float
    {
        $pay = 0;
        $hourlyRate = $this->salary->monthly_salary / (22 * 8);
        $nightRate = (float) ($this->components['nightpay'] ?? 1.1);
    
        foreach ($this->dtrLogs ?? [] as $log) {
            if (strtolower($log->shift) === 'night') {
                $workHours = (float) ($log->total_work_hours ?? 0);
                $overtime = ((int) ($log->overtime_minutes ?? 0)) / 60;
                $regularHours = max(0, $workHours - $overtime);
                $pay += $regularHours * $hourlyRate * ($nightRate - 1);
            }
        }
    
        return $pay;
    }    

    public function calculateOvertimeFromDtrLogs(): float
    {
        $totalOvertimeMinutes = 0;
        if ($this->dtrLogs) {
            foreach ($this->dtrLogs as $log) {
                $totalOvertimeMinutes += max(0, $log->overtime_minutes - $log->grace_overtime_minutes);
            }
        }
        $overtimeHours = $totalOvertimeMinutes / 60;
        $hourlyRate = $this->salary->monthly_salary / (22 * 8);
        $rate = (float) ($this->components['ot_regular'] ?? 1.25);
        return $overtimeHours * $hourlyRate * $rate;
    }

    public function calculateIncomeTax(float $monthlySalary): float
    {
        $annualSalary = $monthlySalary * 12;
        $annualTax = 0;

        if ($annualSalary <= 250000) {
            $annualTax = 0;
        } elseif ($annualSalary <= 400000) {
            $annualTax = ($annualSalary - 250000) * 0.15;
        } elseif ($annualSalary <= 800000) {
            $annualTax = 22500 + ($annualSalary - 400000) * 0.20;
        } elseif ($annualSalary <= 2000000) {
            $annualTax = 102500 + ($annualSalary - 800000) * 0.25;
        } elseif ($annualSalary <= 8000000) {
            $annualTax = 402500 + ($annualSalary - 2000000) * 0.30;
        } else {
            $annualTax = 2202500 + ($annualSalary - 8000000) * 0.35;
        }

        return round($annualTax / 12, 2);
    }

    public function computePayroll(array $data): array
    {
        $basicPay = $this->calculateBasicPayFromDtrLogs();
        $nightPay = $this->calculateNightDiffFromDtrLogs();
        $monthlySalary = $basicPay + $nightPay;
    
        $incomeTax = $this->calculateIncomeTax($monthlySalary);
        $sssRate = (float) ($this->components['sss'] ?? 0.045);
        $philhealthRate = (float) ($this->components['philhealth'] ?? 0.025);
        $pagibigRate = (float) ($this->components['pagibig'] ?? 0.01);

        $f_sss = (float) ($this->components['f_sss'] ?? 0);
        $f_philhealth = (float) ($this->components['f_philhealth'] ?? 0);
        $f_pagibig = (float) ($this->components['f_pagibig'] ?? 0);
    
        $sss = $monthlySalary < 1000 ? 0 : ($monthlySalary <= 3250 ? $f_sss : $monthlySalary * $sssRate);
        $philhealth = $monthlySalary < 10000 ? $f_philhealth : $monthlySalary * $philhealthRate;
        $pagibig = $monthlySalary < 1500 ? $f_pagibig : $monthlySalary * $pagibigRate;

        $otherEarnings = $this->calculateGrossPay($data['earnings'] ?? []);
        $overtimeAmount = $this->calculateOvertimeFromDtrLogs();
    
        $grossEarnings = $monthlySalary + $overtimeAmount + $otherEarnings;
        $totalDeductions = $this->calculateDeductions($data['deductions'] ?? []) + $incomeTax + $sss + $philhealth + $pagibig;
        $netPay = $this->calculateNetPay($grossEarnings, $totalDeductions);
    
        return [
            'basic_pay'          => number_format($basicPay, 2, '.', ''),
            'monthly_salary'     => number_format($monthlySalary, 2, '.', ''),
            'overtime_amount'    => number_format($overtimeAmount, 2, '.', ''),
            'night_differential' => number_format($nightPay, 2, '.', ''),
            'raw_earnings'       => $data['earnings'],
            'raw_deductions'     => $data['deductions'],
            'gross_pay'          => number_format($grossEarnings, 2, '.', ''),
            'income_tax'         => number_format($incomeTax, 2, '.', ''),
            'sss'                => number_format($sss, 2, '.', ''),
            'philhealth'         => number_format($philhealth, 2, '.', ''),
            'pagibig'            => number_format($pagibig, 2, '.', ''),
            'total_deduction'    => number_format($totalDeductions, 2, '.', ''),
            'net_pay'            => number_format($netPay, 2, '.', ''),
        ];
    }    
}