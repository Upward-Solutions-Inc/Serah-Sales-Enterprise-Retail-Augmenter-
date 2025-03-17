<?php

namespace App\Services\Hr\Dtr;

use App\Models\Hr\Dtr\DtrConfig;
use App\Models\Hr\Dtr\DtrLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeClockService
{
    public function clockIn()
    {
        $user = Auth::user();
        $currentDateTime = now();
        $currentTime = $currentDateTime->format('H:i:s');
        $currentDate = $currentDateTime->format('Y-m-d');

        // Get Shift Configurations
        $shiftConfig = DtrConfig::first();

        // Determine Shift
        $shift = "Night Shift"; // Default
        $shiftStart = $shiftConfig->night_shift_start ?? '22:00:00';

        if ($currentTime >= $shiftConfig->morning_shift_start && $currentTime < $shiftConfig->afternoon_shift_start) {
            $shift = "Morning Shift";
            $shiftStart = $shiftConfig->morning_shift_start;
        } elseif ($currentTime >= $shiftConfig->afternoon_shift_start && $currentTime < $shiftConfig->night_shift_start) {
            $shift = "Afternoon Shift";
            $shiftStart = $shiftConfig->afternoon_shift_start;
        }

        // Calculate Late Minutes
        $lateMinutes = max(0, Carbon::parse($shiftStart)->diffInMinutes(Carbon::parse($currentTime), false));

        // Save Clock-In Log
        DtrLog::create([
            'user_id' => $user->id,
            'date' => $currentDate,
            'shift' => $shift,
            'clock_in' => $currentDateTime,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => 0, // Default until clock-out
            'total_work_hours' => 0   // Default until clock-out
        ]);

        return [
            'status' => 'success',
            'message' => "Clocked in successfully for $shift!",
            'shift' => $shift,
            'clock_in' => $currentDateTime->format('h:i A'),
            'late_minutes' => $lateMinutes
        ];
    }

    public function clockOut()
    {
        $user = Auth::user();
        $currentDateTime = now();
        $currentDate = $currentDateTime->format('Y-m-d');

        // Find the latest clock-in record for the user (without a clock-out time)
        $log = DtrLog::where('user_id', $user->id)
            ->whereDate('date', $currentDate)
            ->whereNull('clock_out')
            ->first();

        if (!$log) {
            return [
                'status' => 'error',
                'message' => 'No active clock-in found for today!'
            ];
        }

        // Get shift configuration
        $shiftConfig = DtrConfig::first();
        $standardWorkHours = 8; // Default work hours
        $shiftEnd = $shiftConfig->morning_shift_end; // Default shift end

        if ($log->shift === 'Afternoon Shift') {
            $shiftEnd = $shiftConfig->afternoon_shift_out;
        } elseif ($log->shift === 'Night Shift') {
            $shiftEnd = $shiftConfig->night_shift_end;
        }

        // Calculate total work hours
        $clockInTime = Carbon::parse($log->clock_in);
        $clockOutTime = $currentDateTime;
        $totalWorkHours = $clockInTime->diffInMinutes($clockOutTime) / 60; // Convert minutes to hours

        // Calculate overtime
        $shiftEndTime = Carbon::parse($shiftEnd);
        $overtimeMinutes = max(0, $clockOutTime->diffInMinutes($shiftEndTime, false));

        // Update log with clock-out time, total work hours, and overtime
        $log->update([
            'clock_out' => $currentDateTime,
            'total_work_hours' => round($totalWorkHours, 2),
            'overtime_minutes' => $overtimeMinutes
        ]);

        return [
            'status' => 'success',
            'message' => "Clocked out successfully!",
            'clock_out' => $clockOutTime->format('h:i A'),
            'total_hours' => round($totalWorkHours, 2) . ' hrs',
            'overtime' => $overtimeMinutes . ' mins'
        ];
    }
}