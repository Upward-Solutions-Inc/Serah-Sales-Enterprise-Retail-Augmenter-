<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hr\Dtr\DtrConfig;

class ConfigurationController extends Controller
{
    public function index()
    {
        // Fetch the latest updated configuration
        $dtrConfig = DtrConfig::orderBy('updated_at', 'desc')->first() ?? new DtrConfig();
        // dd($dtrConfig->toArray());
        return view('custom.dtr.configuration', compact('dtrConfig'));
    }
}