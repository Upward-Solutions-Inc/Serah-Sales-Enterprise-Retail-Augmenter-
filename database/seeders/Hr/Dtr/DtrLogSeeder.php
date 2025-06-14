<?php

namespace Database\Seeders\Hr\Dtr;

use Illuminate\Database\Seeder;
use App\Models\Hr\Dtr\DtrLog;
use Carbon\Carbon;

class DtrLogSeeder extends Seeder
{
    public function run()
    {
        DtrLog::truncate(); // Clear previous data

        DtrLog::create([
            'user_id' => 1, // Assuming user exists
            'date' => '2025-03-18',
            'shift' => 'Morning',
            'clock_in' => Carbon::parse('2025-03-18 08:10:00'),
            'clock_out' => Carbon::parse('2025-03-18 12:00:00'),
            'grace_late_minutes' => 5,
            'late_minutes' => 10,
            'grace_overtime_minutes' => 0,
            'overtime_minutes' => 0,
            'total_work_hours' => 3.83,
        ]);

        DtrLog::create([
            'user_id' => 1,
            'date' => '2025-03-18',
            'shift' => 'Afternoon',
            'clock_in' => Carbon::parse('2025-03-18 13:00:00'),
            'clock_out' => Carbon::parse('2025-03-18 17:30:00'),
            'grace_late_minutes' => 5,
            'late_minutes' => 0,
            'grace_overtime_minutes' => 10,
            'overtime_minutes' => 30,
            'total_work_hours' => 4.50,
        ]);;
    }
}