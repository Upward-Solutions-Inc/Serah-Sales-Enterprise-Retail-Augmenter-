<?php

namespace App\Services\Hr\Dtr;

use App\Events\DtrLogUpdated;
use App\Events\AttendanceLogUpdated;
use App\Models\Hr\Dtr\DtrConfig;
use App\Models\Hr\Dtr\DtrLog;
use Carbon\Carbon;

class TimeClockService
{

    public function clockIn($user)
    {
        $now = now();
        $currentDate = $now->toDateString();
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());

        $morningEnd = Carbon::parse($shiftConfig->morning_shift_end)->setDateFrom($now);
        $afternoonEnd = Carbon::parse($shiftConfig->afternoon_shift_end)->setDateFrom($now);
        $nightEnd = Carbon::parse($shiftConfig->night_shift_end)->setDateFrom($now);

        // Adjust night_end to tomorrow if current time is before it
        if ($now->lt($nightEnd)) {
            $nightEnd->subDay();
        }

        // Determine shift based on what end time the clock-in passed
        $shift = match (true) {
            $now->gt($nightEnd) && $now->lte($morningEnd) => 'Morning',
            $now->gt($morningEnd) && $now->lte($afternoonEnd) => 'Afternoon',
            $now->gt($afternoonEnd) || $now->lte($nightEnd->copy()->addDay()) => 'Night',
            default => 'Night'
        };

        // Assign correct shift start for late calc
        $shiftStart = match ($shift) {
            'Morning' => Carbon::parse($shiftConfig->morning_shift_start)->setDateFrom($now),
            'Afternoon' => Carbon::parse($shiftConfig->afternoon_shift_start)->setDateFrom($now),
            'Night' => Carbon::parse($shiftConfig->night_shift_start)
                ->setDate($now->hour < 6 ? $now->subDay()->year : $now->year, $now->month, $now->day)
        };

        $lateMinutes = $now->gt($shiftStart) ? $shiftStart->diffInMinutes($now) : 0;

        DtrLog::create([
            'user_id' => $user->id,
            'date' => $currentDate,
            'shift' => $shift,
            'clock_in' => $now,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => 0,
            'total_work_hours' => 0,
        ]);

        broadcast(new DtrLogUpdated());
        broadcast(new AttendanceLogUpdated($user->id));

        return [
            'status' => 'success',
            'message' => "Clocked in successfully for $shift!",
            'shift' => $shift,
            'clock_in' => $now->format('h:i A'),
            'late_minutes' => $lateMinutes
        ];
    }

    public function clockOut($user, $overrideTime = null)
    {
        $clockOutTime = $overrideTime ? Carbon::parse($overrideTime) : now();
    
        $log = DtrLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'desc')
            ->first();
    
        if (!$log) {
            return ['status' => 'error', 'message' => 'No active clock-in found!'];
        }
    
        $clockInTime = Carbon::parse($log->clock_in);
    
        $shiftConfig = cache()->remember('dtr_config', now()->addMinutes(10), fn() => DtrConfig::first());
        if (!$shiftConfig) {
            return ['status' => 'error', 'message' => 'Shift configuration not found.'];
        }
    
        $shiftKey = strtolower($log->shift) . '_shift';
        $rawStart = $shiftConfig->{$shiftKey . '_start'};
        $rawEnd = $shiftConfig->{$shiftKey . '_end'};
    
        $shiftStartTime = Carbon::parse($rawStart)->setDate(
            $clockInTime->year, $clockInTime->month, $clockInTime->day
        );
    
        $shiftEndTime = Carbon::parse($rawEnd)->setDate(
            $clockInTime->year, $clockInTime->month, $clockInTime->day
        );
    
        if ($shiftEndTime->lessThan($shiftStartTime)) {
            $shiftEndTime->addDay(); // Overnight
        }
    
        // Deadtime ranges
        $deadTimeRanges = [
            [
                'start' => Carbon::parse($shiftConfig->morning_shift_end)->setDateFrom($clockInTime),
                'end' => Carbon::parse($shiftConfig->afternoon_shift_start)->setDateFrom($clockInTime)
            ],
            [
                'start' => Carbon::parse($shiftConfig->afternoon_shift_end)->setDateFrom($clockInTime),
                'end' => Carbon::parse($shiftConfig->night_shift_start)->setDateFrom($clockInTime)
            ],
            [
                'start' => Carbon::parse($shiftConfig->night_shift_end)->setDateFrom($clockInTime),
                'end' => Carbon::parse($shiftConfig->morning_shift_start)
                    ->addDay()
                    ->setDate($clockInTime->year, $clockInTime->month, $clockInTime->day)
            ]
        ];             
    
        foreach ($deadTimeRanges as $dead) {
            if (
                $clockInTime->between($dead['start'], $dead['end']) &&
                $clockOutTime->between($dead['start'], $dead['end'])
            ) {
                $log->delete();
                return [
                    'status' => 'error',
                    'message' => 'Invalid clock-out. Entry deleted because both clock-in & clock-out occurred in deadtime.'
                ];
            }
        }        
    
        // ✅ Final corrected overtime logic
        $overtimeMinutes = $clockOutTime->greaterThan($shiftEndTime)
        ? $shiftEndTime->diffInMinutes($clockOutTime)
        : 0;
    
        $actualStart = $clockInTime->lessThan($shiftStartTime)
            ? $shiftStartTime
            : $clockInTime;
        
        $totalWorkMinutes = $actualStart->diffInMinutes($clockOutTime);
        $totalWorkHours = round($totalWorkMinutes / 60, 2);
    
        $log->update([
            'clock_out' => $clockOutTime,
            'overtime_minutes' => $overtimeMinutes,
            'total_work_hours' => $totalWorkHours
        ]);
    
        broadcast(new DtrLogUpdated());
        broadcast(new AttendanceLogUpdated($user->id));
    
        return [
            'status' => 'success',
            'message' => 'Clocked out successfully!',
            'clock_out' => $clockOutTime->format('h:i A'),
            'overtime' => "$overtimeMinutes min",
            'total_hours' => $totalWorkHours
        ];
    }      
}