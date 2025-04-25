<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Hr\Dtr\TimeClockService;
use App\Models\Hr\Dtr\DtrLog;
use Carbon\Carbon;

class AutoClockOut extends Command
{

    protected $signature = 'auto:clockout';
    protected $description = 'Force clock-out for users who forgot to clock out';
    protected $timeClockService;

    public function __construct(TimeClockService $timeClockService)
    {
        parent::__construct();
        $this->timeClockService = $timeClockService;
    }

    public function handle()
    {
        $openLogs = DtrLog::with('user')
            ->whereNull('clock_out')
            ->get();

            foreach ($openLogs as $log) {
                $user = $log->user;
                if (!$user) continue;
    
                // Set forced clock-out time based on shift
                $forcedTime = match ($log->shift) {
                    'Morning', 'Afternoon' => Carbon::today()->startOfDay()->format('H:i:s'), // 12:00 AM
                    'Night' => Carbon::today()->setTime(12, 0)->format('H:i:s'), // 12:00 PM
                    default => now()->format('H:i:s')
                };
    
                $this->timeClockService->clockOut($user, $forcedTime);
                $this->info("Forced clock-out for: {$user->name} ({$log->shift}) at {$forcedTime}");
            }

        $this->info('Auto clock-out complete.');
    }
}
