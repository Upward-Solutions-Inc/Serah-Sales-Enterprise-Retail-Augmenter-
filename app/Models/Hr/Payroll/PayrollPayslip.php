<?php

namespace App\Models\Hr\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollPayslip extends Model
{
    protected $table = 'payroll_payslips';

    protected $fillable = [
        'user_id',
        'payroll_count_id',
        'basic_pay',
        'overtime_pay',
        'night_differential',
        'allowance',
        'income_tax',
        'sss',
        'pagibig',
        'philhealth',
        'earnings',
        'deductions',
        'gross',
        'net',
    ];

    protected $casts = [
        'earnings' => 'array',
        'deductions' => 'array',
    ];

    public function payrollCount()
    {
        return $this->belongsTo(PayrollCount::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Core\Auth\User::class);
    }
}