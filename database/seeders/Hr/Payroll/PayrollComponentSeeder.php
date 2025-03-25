<?php

namespace Database\Seeders\Hr\Payroll;

use Illuminate\Database\Seeder;
use App\Models\Hr\Payroll\PayrollComponent;

class PayrollComponentSeeder extends Seeder
{
    public function run()
    {
        $components = [
            ['group' => 'rate_basic',    'code' => 'workday',     'label' => 'Regular Workday',     'value' => 1.0,    'is_rate' => 1],
            ['group' => 'rate_basic',    'code' => 'restday',     'label' => 'Regular Restday',     'value' => 1.3,    'is_rate' => 1],
            ['group' => 'rate_basic',    'code' => 'holiday',     'label' => 'Regular Holiday',     'value' => 2.0,    'is_rate' => 1],
            ['group' => 'rate_overtime', 'code' => 'ot_regular',  'label' => 'Regular Overtime',    'value' => 1.25,    'is_rate' => 1],
            ['group' => 'rate_overtime', 'code' => 'ot_restday',  'label' => 'Rest Day Overtime',   'value' => 1.69,    'is_rate' => 1],
            ['group' => 'rate_overtime', 'code' => 'ot_holiday',  'label' => 'Holiday Overtime',    'value' => 2.6,    'is_rate' => 1],
            ['group' => 'rate_deduction','code' => 'sss',         'label' => 'Social Security System', 'value' => 0.045, 'is_rate' => 1],
            ['group' => 'rate_deduction','code' => 'philhealth',  'label' => 'PhilHealth',          'value' => 0.025, 'is_rate' => 1],
            ['group' => 'rate_deduction','code' => 'pagibig',     'label' => 'Pagibig',             'value' => 0.02,   'is_rate' => 1],
            ['group' => 'earnings',      'code' => 'allowance',   'label' => 'Allowance',           'value' => 1500,   'is_rate' => 0],
        ];

        foreach ($components as $item) {
            PayrollComponent::create($item);
        }
    }
}
