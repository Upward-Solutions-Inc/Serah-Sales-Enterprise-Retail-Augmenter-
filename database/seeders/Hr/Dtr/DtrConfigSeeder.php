<?php

namespace Database\Seeders\Hr\Dtr;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DtrConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('dtr_config')->insert([
            'grace_period_morning' => '08:05:00',
            'morning_shift_start' => '08:00:00',
            'morning_shift_end' => '12:00:00',
            'grace_period_afternoon' => '13:05:00',
            'afternoon_shift_start' => '13:00:00',
            'afternoon_shift_out' => '17:00:00',
            'overtime_threshold' => '17:30:00',
            'grace_period_night' => '17:05:00',
            'night_shift_start' => '17:00:00',
            'night_shift_end' => '03:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
