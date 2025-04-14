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
        return view('custom.dtr.time_clock');
    }

    public function getLogs()
    {
        $logs = DtrLog::with('user')->orderBy('updated_at', 'desc')->get();
        return response()->json($logs);
    }

    public function clock(Request $request)
    {   
        $user = $this->verifyUser($request->input('user_id'));

        $dates = [now()->format('Y-m-d'), now()->subDay()->format('Y-m-d')];

        $hasOpenLog = DtrLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->whereIn('date', $dates)
            ->exists();

        $response = $hasOpenLog
            ? $this->timeClockService->clockOut($user)
            : $this->timeClockService->clockIn($user);

        return response()->json($response);
    }

    private function verifyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            abort(response()->json([
                'status' => 'error',
                'message' => 'User is not a registered employee.'
            ], 404));
        }
        return $user;
    }

    public function uploadFace(Request $request)
    {
        $imageBase64 = $request->input('image');
        $userId = $request->input('user_id');
    
        $image = str_replace('data:image/jpeg;base64,', '', $imageBase64);
        $image = str_replace(' ', '+', $image);
        $imageName = 'face_' . now()->timestamp . '.jpg';
    
        $folderPath = "faces/{$userId}";
    
        // Ensure the directory exists (Laravel will auto-create it)
        Storage::disk('public')->put("{$folderPath}/{$imageName}", base64_decode($image));
    
        return response()->json([
            'status' => 'success',
            'path' => "storage/{$folderPath}/{$imageName}"
        ]);
    }    
    
}