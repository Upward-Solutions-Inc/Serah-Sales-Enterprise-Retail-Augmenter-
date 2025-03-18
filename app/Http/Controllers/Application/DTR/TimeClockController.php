<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use App\Services\Hr\Dtr\TimeClockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TimeClockController extends Controller
{   
    protected $timeClockService;

    public function __construct(TimeClockService $timeClockService)
    {
        $this->timeClockService = $timeClockService;
    }

    public function index()
    {   
        $logs = \App\Models\Hr\Dtr\DtrLog::with('user')->orderBy('created_at', 'desc')->get();
        return view('custom.dtr.time_clock', compact('logs'));
    }

    public function clockIn(Request $request)
    {
        $response = $this->timeClockService->clockIn();
        return response()->json($response);
    }

    public function clockOut(Request $request)
    {
        $response = $this->timeClockService->clockOut();
        return response()->json($response);
    }

    public function clockStatus()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
    
            $currentDate = now()->format('Y-m-d');
    
            $logExists = \App\Models\Hr\Dtr\DtrLog::where('user_id', $user->id)
                ->whereDate('date', $currentDate)
                ->whereNull('clock_out')
                ->exists();
    
            return response()->json([
                'clocked_in' => $logExists
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Clock status error: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong! Check logs.'], 500);
        }
    }

    public function getLogs()
    {
        $logs = \App\Models\Hr\Dtr\DtrLog::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json($logs);
    }
    
}