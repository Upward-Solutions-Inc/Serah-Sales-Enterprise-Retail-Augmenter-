<?php

namespace Database\Seeders\Hr\Payroll;

use Illuminate\Database\Seeder;
use App\Models\Hr\Payroll\PayrollComponent;

class PayrollComponentSeeder extends Seeder
{
    public function run()
    {
        $components = [
            ['group' => 'rates',    'code' => 'nightpay',     'label' => 'Night Differential Pay',     'value' => 1.10,    'is_rate' => 1],
            ['group' => 'rates',    'code' => 'restpay',     'label' => 'Restday Pay',     'value' => 1.30,    'is_rate' => 1],
            ['group' => 'rates',    'code' => 'holiday',     'label' => 'Holiday Pay',     'value' => 2.00,    'is_rate' => 1],
            ['group' => 'rates', 'code' => 'ot_regular',  'label' => 'Regular Overtime',    'value' => 1.25,    'is_rate' => 1],
            ['group' => 'rates', 'code' => 'ot_restday',  'label' => 'Rest Day Overtime',   'value' => 1.69,    'is_rate' => 1],
            ['group' => 'rates', 'code' => 'ot_holiday',  'label' => 'Holiday Overtime',    'value' => 2.60,    'is_rate' => 1],
            ['group' => 'rates','code' => 'sss',         'label' => 'Social Security System', 'value' => 0.045, 'is_rate' => 1],
            ['group' => 'rates','code' => 'philhealth',  'label' => 'PhilHealth',          'value' => 0.025, 'is_rate' => 1],
            ['group' => 'rates','code' => 'pagibig',     'label' => 'Pagibig',             'value' => 0.02,   'is_rate' => 1],
            ['group' => 'earnings',      'code' => 'allowance',   'label' => 'Allowance',           'value' => 1500,   'is_rate' => 0],
        ];

        foreach ($components as $item) {
            PayrollComponent::create($item);
        }
    }
}
