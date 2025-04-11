<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use App\Services\Hr\Dtr\TimeClockService;
use App\Models\Core\Auth\User;
use App\Models\Hr\Dtr\DtrLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TimeClockController extends Controller
{   
    protected $timeClockService;

    public function __construct(TimeClockService $timeClockService)
    {
        $this->timeClockService = $timeClockService;
    }

    public function index()
    {   
        $logs = DtrLog::with('user')->orderBy('created_at', 'desc')->get();
        return view('custom.dtr.time_clock', compact('logs'));
    }

    public function getLogs()
    {
        $logs = DtrLog::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($logs);
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
    
            $logExists = DtrLog::where('user_id', $user->id)
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

    public function uploadFace(Request $request)
    {
        $data = $request->input('image');
        $image = str_replace('data:image/jpeg;base64,', '', $data);
        $image = str_replace(' ', '+', $image);
        $imageName = 'face_' . now()->timestamp . '.jpg';

        $folderPath = "faces";

        Storage::disk('public')->put("{$folderPath}/{$imageName}", base64_decode($image));

        return response()->json([
            'status' => 'success',
            'path' => "storage/{$folderPath}/{$imageName}"
        ]);
    }

    

    // To be FIX
    // public function uploadFace(Request $request)
    // {
    //     dd('hit');
    //     file_put_contents(storage_path('logs/debug.txt'), json_encode($request->all()));
        
    //     $userId = intval($request->input('user_id'));
    //     $data = $request->input('image');
    
    //     $this->verifyUser($userId); // ensure user exists
    
    //     $image = str_replace('data:image/jpeg;base64,', '', $data);
    //     $image = str_replace(' ', '+', $image);
    //     $imageName = 'face_' . now()->timestamp . '.jpg';
    
    //     $folderPath = "faces/{$userId}";
    
    //     if (!Storage::disk('public')->exists($folderPath)) {
    //         Storage::disk('public')->makeDirectory($folderPath);
    //     }
    
    //     Storage::disk('public')->put("{$folderPath}/{$imageName}", base64_decode($image));
    
    //     return response()->json([
    //         'status' => 'success',
    //         'path' => "storage/{$folderPath}/{$imageName}"
    //     ]);
    // }

    // private function verifyUser($id)
    // {
    //     $user = User::find($id);
    //     if (!$user) {
    //         abort(response()->json([
    //             'status' => 'error',
    //             'message' => 'User is not a registered employee.'
    //         ], 404));
    //     }
    //     return $user;
    // }
    
}