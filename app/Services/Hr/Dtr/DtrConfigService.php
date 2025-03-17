<?php

namespace App\Services\Hr\Dtr;

use App\Models\Hr\Dtr\DtrConfig;
use Carbon\Carbon;

class DtrConfigService
{
    public function saveConfig(array $data)
    {
        // Convert time fields from 12-hour format to MySQL 24-hour format
        $timeFields = [
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

        foreach ($timeFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Carbon::createFromFormat('h:i A', $data[$field])->format('H:i:s');
            }
        }

        $dtrConfig = DtrConfig::firstOrNew([]);
        $dtrConfig->fill($data);
        $dtrConfig->save();

        return $dtrConfig;
    }
}