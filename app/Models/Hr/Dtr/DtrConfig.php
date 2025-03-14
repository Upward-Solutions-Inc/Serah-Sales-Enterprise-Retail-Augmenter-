<?php

namespace App\Models\Hr\Dtr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DtrConfig extends Model
{
    use HasFactory;

    protected $table = 'dtr_config'; // Table name

    protected $fillable = [
        'grace_period_morning',
        'morning_shift_start',
        'morning_shift_end',
        'grace_period_afternoon',
        'afternoon_shift_start',
        'afternoon_shift_out',
        'overtime_threshold',
        'grace_period_night',
        'night_shift_start',
        'night_shift_end'
    ];

    // Accessors for formatting time
    public function getGracePeriodMorningAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getMorningShiftStartAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getMorningShiftEndAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getGracePeriodAfternoonAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getAfternoonShiftStartAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getAfternoonShiftOutAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getOvertimeThresholdAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getGracePeriodNightAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getNightShiftStartAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getNightShiftEndAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }
}