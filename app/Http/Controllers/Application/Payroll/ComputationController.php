<?php

namespace App\Http\Controllers\Application\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Hr\Payroll\PayrollComponent;
use App\Models\Hr\Payroll\PayrollSalary;
use App\Models\Core\Auth\Role;
use App\Models\Pos\Inventory\BranchOrWarehouse;
use App\Models\Core\Setting\Setting;
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
        $currencySymbol = Setting::where('name', 'currency_symbol')->value('value');
        return response()->json([
            'components' => $components,
            'salaries'   => $salaries,
            'roles'      => $roles,
            'branches'   => $branches,
            'currency_symbol' => $currencySymbol,
        ]);
    }

    public function updatePay(Request $request)
    {
        $data = $request->only([
            'role_id',
            'branch_id',
            'monthly'
        ]);
        
        $payroll = PayrollSalary::updateOrCreate(
            ['role_id' => $data['role_id'], 'branch_id' => $data['branch_id']],
            ['monthly_salary' => $data['monthly']]
        );
        
        // Refresh and eager-load relationships so the response is complete.
        $payroll->refresh();
        $payroll->load(['role', 'branch']);
        
        return response()->json([$payroll]);
    }

    public function updateRate(Request $request)
    {
        $data = $request->only([
            'nightpay',
            'restpay',
            'holiday',
            'ot_regular',
            'ot_restday',
            'ot_holiday',
            'sss',
            'philhealth',
            'pagibig'
        ]);
        
        $componentFields = [
            'nightpay',
            'restpay',
            'holiday',
            'ot_regular',
            'ot_restday',
            'ot_holiday',
            'sss',
            'philhealth',
            'pagibig'
        ];
        
        $labels = [
            'nightpay'   => 'Night Differential Pay',
            'restpay'    => 'Rest Day Pay',
            'holiday'    => 'Holiday Pay',
            'ot_regular' => 'Regular Overtime',
            'ot_restday' => 'Rest Day Overtime',
            'ot_holiday' => 'Holiday Overtime',
            'sss'        => 'Social Security System',
            'philhealth' => 'PhilHealth',
            'pagibig'    => 'Pagibig'
          ];
          
          foreach ($componentFields as $code) {
              if (isset($data[$code])) {
                  $label = $labels[$code] ?? $code;
                  $newValue = $data[$code];
                  PayrollComponent::updateOrCreate(
                      ['code' => $code, 'group' => 'rates'],
                      [
                        'label' => $label,   
                        'value' => $newValue
                      ]
                  );
              }
          }
          
        // Retrieve updated rate components as an array.
        $updatedRates = PayrollComponent::where('group', 'rates')->get()->toArray();
        
        return response()->json($updatedRates);
    }

    public function updateCompenOrDeduc(Request $request)
    {
        $response = [];

        // Process dynamic earnings.
        if ($request->has('earnings') && is_array($request->earnings)) {
            foreach ($request->earnings as $earning) {
                $component = PayrollComponent::updateOrCreate(
                    ['code' => $earning['label'], 'group' => 'earnings'],
                    ['label' => $earning['label'], 'value' => $earning['amount']]
                );
                $response['earnings'][] = $component;
            }
        }

        // Process dynamic deductions.
        if ($request->has('deductions') && is_array($request->deductions)) {
            foreach ($request->deductions as $deduction) {
                $component = PayrollComponent::updateOrCreate(
                    ['code' => $deduction['label'], 'group' => 'deductions'],
                    ['label' => $deduction['label'], 'value' => $deduction['amount']]
                );
                $response['deductions'][] = $component;
            }
        }

        return response()->json($response);
    }
}