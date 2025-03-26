<?php

namespace App\Models\Hr\Payroll;

use App\Models\Core\Auth\Role;
use App\Models\Pos\Inventory\BranchOrWarehouse;
use Illuminate\Database\Eloquent\Model;

class PayrollSalary extends Model
{
    protected $table = 'payroll_salary';

    protected $fillable = [
        'role_id',
        'branch_id',
        'monthly_salary',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(BranchOrWarehouse::class, 'branch_id', 'id');
    }
}