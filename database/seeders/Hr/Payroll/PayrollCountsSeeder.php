<?php

namespace Database\Seeders\Hr\Payroll;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Hr\Payroll\PayrollCount;

class PayrollCountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payroll_counts')->insert([
            'date_range_start' => now()->subDays(15),
            'date_range_end' => now(),
            'payroll_type' => 'Semi-Monthly',
            'total_employees' => 1,
            'total_basic_pay' => 900000,
            'total_overtime_pay' => 50000,
            'total_night_differential' => 50000,
            'total_earnings' => 1,
            'total_deductions' => 1,
            'total_allowance' => 100000,
            'total_sss' => 50000,
            'total_philhealth' => 30000,
            'total_pagibig' => 20000,
            'total_gross' => 1200000,
            'total_net' => 1100000,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);        
    }
}
