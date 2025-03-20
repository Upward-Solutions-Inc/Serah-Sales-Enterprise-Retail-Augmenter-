<?php

namespace App\Services\Hr\Dtr;

use App\Models\Hr\Dtr\DtrConfig;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DtrConfigService
{
    public function saveConfig(array $data)
    {   
        // dd("Data received in service:", $data);
        Cache::forget('dtr_config');
        // dd(Cache::get('dtr_config'));
        $timeFields = [
            'morning_shift_start',
            'morning_shift_end',
            'afternoon_shift_start',
            'afternoon_shift_end',
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