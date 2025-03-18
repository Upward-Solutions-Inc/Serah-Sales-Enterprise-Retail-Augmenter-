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
        $currentTime = $currentDateTime->format('H:i:s');

        // Get Shift Configurations
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());

        // Determine Shift
        $shift = match (true) {
            $currentDateTime->between(Carbon::parse($shiftConfig->morning_shift_start), Carbon::parse($shiftConfig->morning_shift_end)) => "Morning",
            $currentDateTime->between(Carbon::parse($shiftConfig->afternoon_shift_start), Carbon::parse($shiftConfig->afternoon_shift_out)) => "Afternoon",
            default => "Night",
        };

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
            'clock_in' => Carbon::parse($currentTime)->format('h:i A')
        ];
    }

    public function clockOut()
    {
        $user = Auth::user();
        $currentDateTime = now();
        $currentDate = $currentDateTime->format('Y-m-d');
        $currentTime = $currentDateTime->format('H:i:s');

        // Find the latest clock-in record for the user (without a clock-out time)
        $log = DtrLog::where('user_id', $user->id)
            ->where('date', $currentDate)
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
        $shiftEndTime = match ($log->shift) {
            'Afternoon' => $shiftConfig->afternoon_shift_out,
            'Night' => $shiftConfig->night_shift_end,
            default => $shiftConfig->morning_shift_end,
        };

        // Convert times
        $clockInTime = Carbon::parse($log->clock_in);
        $clockOutTime = Carbon::parse($currentTime);
        $shiftEnd = Carbon::parse($shiftEndTime);

        // Calculate total work hours
        $totalWorkMinutes = $clockInTime->diffInMinutes($clockOutTime);
        $totalWorkHours = round($totalWorkMinutes / 60, 2);

        // Overtime Calculation
        $overtimeMinutes = $clockOutTime->greaterThan($shiftEnd)
            ? $shiftEnd->diffInMinutes($clockOutTime)
            : 0;

        // Update log
        $log->update([
            'clock_out' => $currentTime,
            'total_work_hours' => $totalWorkHours,
            'overtime_minutes' => $overtimeMinutes
        ]);

        // ✅ Broadcast the event
        broadcast(new DtrLogUpdated());

        return [
            'status' => 'success',
            'message' => "Clocked out successfully!",
            'clock_out' => Carbon::parse($currentTime)->format('h:i A'),
            'total_hours' => "$totalWorkHours hrs",
            'overtime' => "$overtimeMinutes mins"
        ];
    }
}