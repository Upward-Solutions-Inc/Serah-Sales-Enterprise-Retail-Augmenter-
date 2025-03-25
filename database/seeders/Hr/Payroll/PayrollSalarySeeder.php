<?php

namespace Database\Seeders\Hr\Payroll;

use Illuminate\Database\Seeder;
use App\Models\Hr\Payroll\PayrollSalary;

class PayrollSalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $salaries = [
            ['role_id' => 1, 'branch_id' => 1, 'monthly_salary' => 35000.00],   // App Admin
            ['role_id' => 2, 'branch_id' => 1, 'monthly_salary' => 30000.00],   // Manager
            ['role_id' => 3, 'branch_id' => 1, 'monthly_salary' => 25000.00],   // Warehouse Manager
            ['role_id' => 4, 'branch_id' => 1, 'monthly_salary' => 20000.00],   // Branch Manager
            ['role_id' => 5, 'branch_id' => 1, 'monthly_salary' => 15000.00],   // Cashier
        ];

        foreach ($salaries as $salary) {
            PayrollSalary::create($salary);
        }
    }
}