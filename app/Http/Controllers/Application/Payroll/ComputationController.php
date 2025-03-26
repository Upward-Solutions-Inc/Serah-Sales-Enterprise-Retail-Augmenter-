<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Hr\Payroll\PayrollComponent;
use App\Models\Hr\Payroll\PayrollSalary;
use App\Models\Core\Auth\Role;
use App\Models\Pos\Inventory\BranchOrWarehouse;
use Illuminate\Http\Request;

class ComputationController extends Controller
{
    public function view()
    {
        return view('custom.payroll.computation');
    }

    public function data() {

        $components = PayrollComponent::all();
        $salaries = PayrollSalary::with(['role', 'branch'])->get();
        $roles = Role::select('id', 'name')->get();
        $branches = BranchOrWarehouse::select('id', 'name')->where('type', 'branch')->get();
        return response()->json([
            'components' => $components,
            'salaries'   => $salaries,
            'roles'      => $roles,
            'branches'   => $branches,
        ]);
    }
}