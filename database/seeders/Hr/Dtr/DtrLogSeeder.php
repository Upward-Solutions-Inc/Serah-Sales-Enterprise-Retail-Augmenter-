<?php

namespace Database\Seeders\Hr\Dtr;

use Illuminate\Database\Seeder;
use App\Models\Hr\Dtr\DtrLog;
use Carbon\Carbon;

class DtrLogSeeder extends Seeder
{
    public function run()
    {
        // Insert Morning Shift for User ID 1
        DtrLog::create([
            'user_id' => 1, // Directly assigning to user ID 1
            'date' => '2025-03-03',
            'shift' => 'Morning',
            'clock_in' => Carbon::createFromFormat('h:i A', '8:10 AM'),
            'clock_out' => Carbon::createFromFormat('h:i A', '12:00 PM'),
            'late_minutes' => 10,
            'overtime_minutes' => 0,
            'total_work_hours' => 3.83, // 3 hours 50 minutes
        ]);

        // Insert Afternoon Shift for User ID 1
        DtrLog::create([
            'user_id' => 1, // Directly assigning to user ID 1
            'date' => '2025-03-03',
            'shift' => 'Afternoon',
            'clock_in' => Carbon::createFromFormat('h:i A', '1:00 PM'),
            'clock_out' => Carbon::createFromFormat('h:i A', '5:30 PM'),
            'late_minutes' => 0,
            'overtime_minutes' => 30,
            'total_work_hours' => 4.5, // 4 hours 30 minutes
        ]);

        echo "✅ DTR logs seeded successfully for User ID 1!\n";
    }
}