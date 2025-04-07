<?php

namespace Database\Seeders\Hr\Payroll;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Hr\Payroll\PayrollPayslip;

class PayrollPayslipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payroll_payslips')->insert([
            [
                'user_id' => 1,
                'payroll_count_id' => 1,
                'allowance' => 1000,
                'sss' => 500,
                'pagibig' => 200,
                'philhealth' => 300,
                'earnings' => json_encode([['component_id' => 2, 'amount' => 1500]]),
                'deductions' => json_encode([['component_id' => 5, 'amount' => 100]]),
                'gross' => 12000,
                'net' => 11000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
    }
}
