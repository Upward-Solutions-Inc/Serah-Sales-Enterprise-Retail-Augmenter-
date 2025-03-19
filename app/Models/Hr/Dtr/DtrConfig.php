<?php

namespace App\Models\Hr\Dtr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DtrConfig extends Model
{
    use HasFactory;

    protected $table = 'dtr_config';

    protected $fillable = [
        'grace_period',
        'overtime',
        'morning_shift_start',
        'morning_shift_end',
        'afternoon_shift_start',
        'afternoon_shift_end',
        'night_shift_start',
        'night_shift_end'
    ];

    // Accessors for formatting time
    public function getMorningShiftStartAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getMorningShiftEndAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getAfternoonShiftStartAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getAfternoonShiftEndAttribute($value)
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

    public function getGracePeriodAttribute($value)
    {
        return "{$value}";
    }

    public function getOvertimeAttribute($value)
    {
        return "{$value}";
    }
}