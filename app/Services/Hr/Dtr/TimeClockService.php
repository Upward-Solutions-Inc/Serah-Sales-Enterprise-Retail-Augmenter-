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
            $currentDateTime->between(Carbon::parse($shiftConfig->afternoon_shift_start), Carbon::parse($shiftConfig->afternoon_shift_end)) => "Afternoon",
            // Night shift crosses midnight, so check both days
            $currentDateTime->greaterThanOrEqualTo(Carbon::parse($shiftConfig->night_shift_start)) || 
            $currentDateTime->lessThan(Carbon::parse($shiftConfig->night_shift_end)->addDay()) => "Night",
            default => "Night",
        };

        // Late Minutes Calculation
        $shiftStartTime = Carbon::parse(match ($shift) {
            "Morning" => $shiftConfig->morning_shift_start,
            "Afternoon" => $shiftConfig->afternoon_shift_start,
            "Night" => $shiftConfig->night_shift_start,
        });

        $clockInTime = Carbon::parse($currentTime);
        $lateMinutes = $clockInTime->greaterThan($shiftStartTime) 
            ? $shiftStartTime->diffInMinutes($clockInTime) 
            : 0;

        // Save Clock-In Log
        DtrLog::create([
            'user_id' => $user->id,
            'date' => $currentDate,
            'shift' => $shift,
            'clock_in' => $currentTime,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => 0,
            'total_work_hours' => 0
        ]);

        // ✅ Broadcast the event
        broadcast(new DtrLogUpdated());

        return [
            'status' => 'success',
            'message' => "Clocked in successfully for $shift!",
            'shift' => $shift,
            'clock_in' => Carbon::parse($currentTime)->format('h:i A'),
            'late_minutes' => $lateMinutes
        ];
    }

    public function clockOut()
    {
        $user = Auth::user();
        $currentDateTime = now();
        $currentTime = $currentDateTime->format('H:i:s');

        // Find the latest clock-in record for the user
        $log = DtrLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'asc')
            ->first();
        if (!$log) { return ['status' => 'error', 'message' => 'No active clock-in found!']; }

        // Prep data variables
        $clockInTime = Carbon::parse($log->clock_in);
        $clockOutTime = Carbon::parse($currentTime);
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());

        $shifts = [
            'Morning' => ['start' => Carbon::parse($shiftConfig->morning_shift_start), 'end' => Carbon::parse($shiftConfig->morning_shift_end)],
            'Afternoon' => ['start' => Carbon::parse($shiftConfig->afternoon_shift_start), 'end' => Carbon::parse($shiftConfig->afternoon_shift_end)],
            'Night' => ['start' => Carbon::parse($shiftConfig->night_shift_start), 'end' => Carbon::parse($shiftConfig->night_shift_end)],
        ];

        // checking deadlock time
        $deadTimeRanges = [];
        $shiftKeys = array_keys($shifts);
        for ($i = 0; $i < count($shiftKeys) - 1; $i++) {
            $deadTimeRanges[] = [
                'start' => $shifts[$shiftKeys[$i]]['end'],
                'end' => $shifts[$shiftKeys[$i + 1]]['start']
            ];
        }

        $isDeadTimeLog = false;
        foreach ($deadTimeRanges as $deadTime) {
            if ($clockInTime->between($deadTime['start'], $deadTime['end']) && 
                $clockOutTime->between($deadTime['start'], $deadTime['end'])) {
                $isDeadTimeLog = true;
                break;
            }
        }

        // Delete log => deadtime
        if ($isDeadTimeLog) {
            $log->delete();
            return [
                'status' => 'error',
                'message' => 'Invalid clock-out. Entry deleted in 5 seconds.',
            ];
        }

        $shiftStartTime = $shifts[$log->shift]['start'];
        $shiftEndTime = $shifts[$log->shift]['end'];

        // Calculate Overtime
        $overtimeMinutes = ($clockOutTime->greaterThan($shiftEndTime) && $clockInTime->greaterThanOrEqualTo($shiftStartTime))
            ? $shiftEndTime->diffInMinutes($clockOutTime)
            : 0;    

        // Calculate total work hours
        $actualStartTime = $clockInTime->lessThan($shiftStartTime) ? $shiftStartTime : $clockInTime;
        $totalWorkHours = round($actualStartTime->diffInMinutes($clockOutTime) / 60, 2);

        // Update log
        $log->update([
            'clock_out' => $currentTime,
            'overtime_minutes' => $overtimeMinutes,
            'total_work_hours' => $totalWorkHours
        ]);

        // Broadcast the event
        broadcast(new DtrLogUpdated());

        return [
            'status' => 'success',
            'message' => "Clocked out successfully!",
            'clock_out' => Carbon::parse($currentTime)->format('h:i A'),
            'overtime' => "$overtimeMinutes min",
            'total_hours' => "$totalWorkHours h"
        ];
    }
}