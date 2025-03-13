<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DtrLog;

class DtrLogSeeder extends Seeder
{
    public function run()
    {
        DtrLog::create([
            'user_id' => 1,
            'date' => now()->toDateString(),
            'morning_in' => '08:10:00',
            'morning_out' => '12:00:00',
            'afternoon_in' => '13:00:00',
            'afternoon_out' => '17:00:00',
            'late_minutes' => 10,
            'total_work_hours' => 7.50
        ]);
    }
}
