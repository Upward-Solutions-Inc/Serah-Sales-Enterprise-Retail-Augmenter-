<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DtrLog extends Model
{
    use HasFactory;

    protected $table = 'dtr_logs';

    protected $fillable = [
        'employee_id',
        'date',
        'morning_in',
        'morning_out',
        'afternoon_in',
        'afternoon_out',
        'late_minutes',
        'total_work_hours',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
