<?php

namespace App\Services\Hr\Dtr;

use App\Events\DtrLogUpdated;
use App\Models\Hr\Dtr\DtrConfig;
use App\Models\Hr\Dtr\DtrLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Carbon\Carbon;

class TimeClockService
{
    public function clockIn()
    {
        $user = Auth::user();
        $currentDateTime = now();
        $currentDate = $currentDateTime->format('Y-m-d');
    
        // Get Shift Configurations
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());
    
        $currentTime = Carbon::now();
        $currentHour = $currentTime->format('h:i A'); // Format as AM/PM
    
        // Convert shift end times for accurate evening detection
        $afternoonShiftEnd = Carbon::parse($shiftConfig->afternoon_shift_out);
    
        // Determine Shift Based on AM/PM Logic
        if ($currentTime->format('A') === 'AM') {
            $shift = "Morning";
        } elseif ($currentTime->format('A') === 'PM' && $currentTime->lessThan($afternoonShiftEnd)) {
            $shift = "Afternoon";
        } else {
            $shift = "Night"; // Default for anything after the afternoon shift end
        }
    
        // Save Clock-In Log
        DtrLog::create([
            'user_id' => $user->id,
            'date' => $currentDate,
            'shift' => $shift,
            'clock_in' => $currentTime,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
            'total_work_hours' => 0
        ]);
    
        // ✅ Broadcast the event
        broadcast(new DtrLogUpdated());
    
        return [
            'status' => 'success',
            'message' => "Clocked in successfully for $shift!",
            'shift' => $shift,
            'clock_in' => $currentDateTime->format('h:i A')
        ];
    }
    
    public function clockOut()
    {
        $user = Auth::user();
        $currentDateTime = now();
        $currentDate = $currentDateTime->format('Y-m-d');

        // Find the latest clock-in record for the user (without a clock-out time)
        $log = DtrLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        if (!$log) {
            return [
                'status' => 'error',
                'message' => 'No active clock-in found for today!'
            ];
        }

        // Get shift configuration
        $shiftConfig = DtrConfig::first();
        $shiftEnd = match ($log->shift) {
            'Afternoon' => $shiftConfig->afternoon_shift_out,
            'Night' => $shiftConfig->night_shift_end,
            default => $shiftConfig->morning_shift_end,
        };

        // Convert times
        $clockInTime = Carbon::parse($log->clock_in);
        $clockOutTime = $currentDateTime;
        $shiftEndTime = Carbon::parse($shiftEnd);

        // Calculate total work hours
        $totalWorkMinutes = $clockInTime->diffInMinutes($clockOutTime);
        $totalWorkHours = round($totalWorkMinutes / 60, 2);

        // ✅ Fix Overtime Calculation (only if clock-out is AFTER shift end)
        $overtimeMinutes = $clockOutTime->greaterThan($shiftEndTime) 
            ? $clockOutTime->diffInMinutes($shiftEndTime) 
            : 0; 

        // Update log
        $log->update([
            'clock_out' => $currentDateTime,
            'total_work_hours' => $totalWorkHours,
            'overtime_minutes' => $overtimeMinutes
        ]);

        // Broadcast the event
        broadcast(new DtrLogUpdated());

        return [
            'status' => 'success',
            'message' => "Clocked out successfully!",
            'clock_out' => $clockOutTime->format('h:i A'),
            'total_hours' => "$totalWorkHours hrs",
            'overtime' => "$overtimeMinutes mins"
        ];
    }
}