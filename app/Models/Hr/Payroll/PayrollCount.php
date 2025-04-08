<?php

namespace App\Models\Hr\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollCount extends Model
{
    protected $table = 'payroll_counts';

    protected $fillable = [
        'date_range_start',
        'date_range_end',
        'payroll_type',
        'total_employees',
        'total_basic_pay',
        'total_overtime_pay',
        'total_night_differential',
        'total_earnings',
        'total_deductions',
        'total_allowance',
        'total_sss',
        'total_philhealth',
        'total_pagibig',
        'total_gross',
        'total_net',
        'created_by',
    ];

    public function payslips()
    {
        return $this->hasMany(PayrollPayslip::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\Core\Auth\User::class, 'created_by');
    }
}