<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Hr\Dtr\DtrConfigService;
use App\Models\Hr\Dtr\DtrConfig;

class ConfigurationController extends Controller
{
    protected $dtrConfigService;

    public function __construct(DtrConfigService $dtrConfigService)
    {
        $this->dtrConfigService = $dtrConfigService;
    }

    public function index()
    {
        return view('custom.dtr.configuration');
    }

    public function getConfig()
    {
        return response()->json(
            DtrConfig::latest('updated_at')->first() ?? new DtrConfig()
        );
    }      

    public function store(Request $request)
    {   
        $validatedData = $request->validate([
            'grace_period' => 'nullable|integer',
            'overtime' => 'nullable|integer',
            'morning_shift_start' => 'nullable|string',
            'morning_shift_end' => 'nullable|string',
            'afternoon_shift_start' => 'nullable|string',
            'afternoon_shift_end' => 'nullable|string',
            'night_shift_start' => 'nullable|string',
            'night_shift_end' => 'nullable|string',
        ]);
        // dd("Data before sending to service:", $validatedData);

        $validatedData = array_filter($validatedData);
        $this->dtrConfigService->saveConfig($validatedData);

        return response()->json(['message' => 'Configuration saved successfully.']);
    }
}