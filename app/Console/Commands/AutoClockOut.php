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

            $forcedTime = match ($log->shift) {
                'Morning', 'Afternoon' => Carbon::parse($log->date)->addDay()->startOfDay()->addMinute()->format('Y-m-d H:i:s'), // 12:01 AM next day
                'Night' => Carbon::parse($log->date)->setTime(12, 1, 0)->format('Y-m-d H:i:s'), // 12:01 PM same day
                default => now()->format('Y-m-d H:i:s')
            };

            $this->timeClockService->clockOut($user, $forcedTime);
            $this->info("Forced clock-out for: {$user->name} ({$log->shift}) at {$forcedTime}");
        }

        $this->info('Auto clock-out complete.');
    }
}