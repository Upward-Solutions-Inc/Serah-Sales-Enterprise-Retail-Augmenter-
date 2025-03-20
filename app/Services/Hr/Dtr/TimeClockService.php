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
        $currentDate = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        // Get Shift Configurations
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());

        $morningShiftStart = Carbon::parse($shiftConfig->morning_shift_start)->hour;
        $morningShiftEnd = Carbon::parse($shiftConfig->morning_shift_end)->format('H:i:s');
        $afternoonShiftEnd = Carbon::parse($shiftConfig->afternoon_shift_end)->format('H:i:s');
        $nightShiftEnd = Carbon::parse($shiftConfig->night_shift_end)->format('H:i:s');

        // Determine Shift
        $shift = match (true) {
            ($currentTime > $nightShiftEnd && $currentTime < $morningShiftEnd) => "Morning",
            ($currentTime >= $morningShiftEnd && $currentTime < $afternoonShiftEnd) => "Afternoon",
            ($currentTime >= $afternoonShiftEnd || $currentTime <= $nightShiftEnd) => "Night",
            default => "Night",
        };

        // Late Minutes Calculation
        $shiftStartDate = $currentDate;
        if ($shift == "Night" && now()->hour < $morningShiftStart) { 
            $shiftStartDate = now()->subDay()->format('Y-m-d'); // Use previous day if after midnight
        }

        $shiftStartTime = Carbon::parse(match ($shift) {
            "Morning" => "$currentDate " . $shiftConfig->morning_shift_start,
            "Afternoon" => "$currentDate " . $shiftConfig->afternoon_shift_start,
            "Night" => "$shiftStartDate " . $shiftConfig->night_shift_start,
        });        

        $lateMinutes = Carbon::parse($currentTime)->greaterThan(Carbon::parse($shiftStartTime))
            ? Carbon::parse($shiftStartTime)->diffInMinutes(Carbon::parse($currentTime))
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
        // $currentDate = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        // Find the latest clock-in record for the user
        $log = DtrLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'desc')
            ->first();
        if (!$log) { return ['status' => 'error', 'message' => 'No active clock-in found!']; }

        // Prep data variables
        $clockInTime = Carbon::parse($log->clock_in);
        $clockOutTime = now();
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());

        $shifts = [
            'Morning' => ['start' => Carbon::parse($shiftConfig->morning_shift_start), 'end' => Carbon::parse($shiftConfig->morning_shift_end)],
            'Afternoon' => ['start' => Carbon::parse($shiftConfig->afternoon_shift_start), 'end' => Carbon::parse($shiftConfig->afternoon_shift_end)],
            'Night' => ['start' => Carbon::parse($shiftConfig->night_shift_start), 'end' => Carbon::parse($shiftConfig->night_shift_end)],
        ];

        // checking deadlock time
        $deadTimeRanges = [];
        $shiftKeys = array_keys($shifts);
        for ($i = 0; $i < count($shiftKeys); $i++) {
            $currentShiftEnd = $shifts[$shiftKeys[$i]]['end'];
            $nextShiftStart = $shifts[$shiftKeys[($i + 1) % count($shifts)]]['start'];
    
            // Create deadtime gap between shift end & next shift start
            $deadTimeRanges[] = [
                'start' => $currentShiftEnd,
                'end' => $nextShiftStart
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
                'message' => 'Invalid clock-out. Entry deleted because both clock-in & clock-out occurred in deadtime.'
            ];
        }






        // // Overtime Calculation
        // $clockInTime = Carbon::parse($log->clock_in);
        // $clockOutTime = Carbon::parse($currentTime);
        // $shiftEnd = Carbon::parse($shiftEndTime);

        // $overtimeMinutes = $clockOutTime->greaterThan($shiftEnd)
        // ? $shiftEnd->diffInMinutes($clockOutTime)
        // : 0;

        // // Calculate total work hours
        // $totalWorkMinutes = $clockInTime->diffInMinutes($clockOutTime);
        // $totalWorkHours = round($totalWorkMinutes / 60, 2);







        // Calculate Overtime
        $shiftStartTime = $shifts[$log->shift]['start'];
        $shiftEndTime = $shifts[$log->shift]['end'];

        if ($shiftEndTime->lessThan($shiftStartTime)) { 
            $shiftEndTime->addDay(); // Fix overnight shifts
        }

        if ($clockOutTime->lessThan($clockInTime)) {
            $clockOutTime->addDay(); // Fix cases where clock-out is after midnight
        }

        $overtimeMinutes = $clockOutTime->greaterThan($shiftEndTime)
            ? $shiftEndTime->diffInMinutes($clockOutTime)
            : 0; 

        // Calculate total work hours
        $actualStartTime = $clockInTime->lessThan($shiftStartTime) ? $shiftStartTime : $clockInTime;
        $totalWorkMinutes = $actualStartTime->diffInMinutes($clockOutTime);
        $totalWorkHours = round($totalWorkMinutes / 60, 2);

        // $actualStartTime = $clockInTime->lessThan($shiftStartTime) ? $shiftStartTime : $clockInTime;
        // $totalWorkHours = round($actualStartTime->diffInMinutes($clockOutTime) / 60, 2);







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
            'total_hours' => $totalWorkHours
        ];
    }
}