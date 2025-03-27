<?php

namespace App\Services\Payroll;

use App\Models\Hr\Payroll\PayrollSalary;
use App\Models\Hr\Payroll\PayrollComponent;
use App\Models\Hr\Dtr\DtrLog;

class PayrollService
{
    /**
     * The PayrollSalary model instance.
     *
     * @var PayrollSalary
     */
    protected $salary;

    /**
     * Payroll components, such as rate values.
     *
     * @var array
     */
    protected $components;

    /**
     * DTR logs collection.
     *
     * @var \Illuminate\Support\Collection|null
     */
    protected $dtrLogs;

    /**
     * Constructor.
     *
     * @param PayrollSalary $salary
     * @param array $components
     * @param \Illuminate\Support\Collection|null $dtrLogs
     */
    public function __construct(PayrollSalary $salary, array $components = [], $dtrLogs = null)
    {
        $this->salary = $salary;
        $this->components = $components;
        $this->dtrLogs = $dtrLogs;
    }

    /**
     * Calculate gross pay from earnings.
     *
     * @param array $earnings
     * @return float
     */
    public function calculateGrossPay(array $earnings): float
    {
        return array_sum($earnings);
    }

    /**
     * Calculate total deductions.
     *
     * @param array $deductions
     * @return float
     */
    public function calculateDeductions(array $deductions): float
    {
        return array_sum($deductions);
    }

    /**
     * Calculate net pay.
     *
     * @param float $gross
     * @param float $deductions
     * @return float
     */
    public function calculateNetPay(float $gross, float $deductions): float
    {
        return $gross - $deductions;
    }

    /**
     * Calculate overtime amount based on DTR logs.
     *
     * @return float
     */
    public function calculateOvertimeFromDtrLogs(): float
    {
        $totalOvertimeMinutes = 0;
        if ($this->dtrLogs) {
            foreach ($this->dtrLogs as $log) {
                $totalOvertimeMinutes += $log->overtime_minutes;
            }
        }
        // Convert minutes to hours.
        $overtimeHours = $totalOvertimeMinutes / 60;
        // Assume hourly rate = monthly salary divided by (22 workdays * 8 hours)
        $hourlyRate = $this->salary->monthly_salary / (22 * 8);
        return $overtimeHours * $hourlyRate;
    }

    /**
     * Compute payroll based on earnings, deductions, and DTR logs.
     *
     * @param array $data  // Should include 'earnings' and 'deductions' arrays.
     * @return array
     */
    public function computePayroll(array $data): array
    {
        // Retrieve the monthly salary from the PayrollSalary model
        $monthlySalary = (float) $this->salary->monthly_salary;

        // Retrieve dynamic rates from components or use defaults.
        $sssRate        = isset($this->components['sss']) ? (float) $this->components['sss'] : 0.045;
        $philhealthRate = isset($this->components['philhealth']) ? (float) $this->components['philhealth'] : 0.025;
        $pagibigRate    = isset($this->components['pagibig']) ? (float) $this->components['pagibig'] : 0.02;

        // Compute deductions based on the monthly salary and dynamic rates.
        $sss = $monthlySalary * $sssRate;
        $philhealth = $monthlySalary * $philhealthRate;
        $pagibig = $monthlySalary < 1500 ? 100 : $monthlySalary * $pagibigRate;

        // Incorporate overtime computed from DTR logs.
        $overtimeAmount = $this->calculateOvertimeFromDtrLogs();

        // Sum up additional earnings (like overtime) if provided.
        $grossEarnings = $this->calculateGrossPay($data['earnings'] ?? []) + $overtimeAmount;

        // Total deductions include both manual and computed ones.
        $totalDeductions = $this->calculateDeductions($data['deductions'] ?? []) + $sss + $philhealth + $pagibig;

        // Compute net pay.
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