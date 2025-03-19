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
            'grace_period' => 5,
            'overtime' => 15,
            'morning_shift_start' => '08:00:00',
            'morning_shift_end' => '12:00:00',
            'afternoon_shift_start' => '13:00:00',
            'afternoon_shift_end' => '17:00:00',
            'night_shift_start' => '17:00:00',
            'night_shift_end' => '03:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
