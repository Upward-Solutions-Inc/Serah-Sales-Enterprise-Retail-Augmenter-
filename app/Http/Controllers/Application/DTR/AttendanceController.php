<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use App\Services\Hr\Dtr\TimeClockService;
use App\Models\Core\Auth\User;
use App\Models\Hr\Dtr\DtrLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('custom.dtr.attendance');
    }

    public function getLogs()
    {   
        $userId = auth()->id();
        $logs = DtrLog::with('user')
        ->where('user_id', $userId)
        ->orderBy('updated_at', 'desc')
        ->get();
        return response()->json($logs);
    }
}