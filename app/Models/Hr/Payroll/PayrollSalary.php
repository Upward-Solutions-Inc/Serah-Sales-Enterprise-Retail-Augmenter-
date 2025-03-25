<?php

namespace App\Models\Hr\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollSalary extends Model
{
    protected $table = 'payroll_salary';

    protected $fillable = [
        'role_id',
        'branch_id',
        'monthly_salary',
    ];
}