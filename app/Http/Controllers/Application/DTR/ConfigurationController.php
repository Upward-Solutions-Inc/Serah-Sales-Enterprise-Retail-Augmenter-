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
        $dtrConfig = DtrConfig::orderBy('updated_at', 'desc')->first() ?? new DtrConfig();
        // dd($dtrConfig->toArray());
        return view('custom.dtr.configuration', compact('dtrConfig'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'grace_period_morning' => 'nullable|string',
            'morning_shift_start' => 'nullable|string',
            'morning_shift_end' => 'nullable|string',
            'grace_period_afternoon' => 'nullable|string',
            'afternoon_shift_start' => 'nullable|string',
            'afternoon_shift_out' => 'nullable|string',
            'overtime_threshold' => 'nullable|string',
            'grace_period_night' => 'nullable|string',
            'night_shift_start' => 'nullable|string',
            'night_shift_end' => 'nullable|string',
        ]);

        $validatedData = array_filter($validatedData);
        $this->dtrConfigService->saveConfig($validatedData);
        return redirect()->route('config.configuration')->with('success', 'Configuration saved successfully.');
    }
}