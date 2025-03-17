<?php

namespace App\Models\Hr\Dtr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrLog extends Model
{
    use HasFactory;

    protected $table = 'dtr_logs';

    protected $fillable = [
        'user_id',
        'date',
        'shift',
        'clock_in',
        'clock_out',
        'late_minutes',
        'overtime_minutes',
        'total_work_hours',
    ];

    /**
     * Get the user that owns this log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\Auth\User::class);
    }
}