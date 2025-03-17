<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TimeClockController extends Controller
{
    public function index()
    {   
        return view('custom.dtr.time_clock');
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $timestamp = now();

        Log::info("User {$user->id} clocked in at {$timestamp}");

        return response()->json([
            'status' => 'success',
            'message' => 'Clocked In successfully!',
            'timestamp' => $timestamp
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $timestamp = now();

        Log::info("User {$user->id} clocked out at {$timestamp}");

        return response()->json([
            'status' => 'success',
            'message' => 'Clocked Out successfully!',
            'timestamp' => $timestamp
        ]);
    }
}