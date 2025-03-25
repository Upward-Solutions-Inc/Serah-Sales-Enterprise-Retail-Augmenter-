<?php

namespace App\Models\Hr\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollComponent extends Model
{
    protected $table = 'payroll_components';

    protected $fillable = [
        'group', 
        'code', 
        'label', 
        'value', 
        'is_rate',
    ];
}