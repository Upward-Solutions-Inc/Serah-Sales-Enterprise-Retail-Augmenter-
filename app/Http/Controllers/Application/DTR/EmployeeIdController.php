<?php

namespace App\Http\Controllers\Application\DTR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Core\Auth\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;


class EmployeeIdController extends Controller
{

    public function index()
    {
        return view('custom.dtr.employee_id');
    }


    public function generateUserQrFromData(Request $request)
    {
        $data = json_encode([
            'id'     => $request->id,
            'name'   => $request->name,
            'email'  => $request->email,
            'role'   => $request->role,
            'branch' => $request->branch,
        ]);
    
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
    
        $writer = new Writer($renderer);
        $svg = $writer->writeString($data);
    
        return response($svg)
            ->header('Content-Type', 'image/svg+xml');
    }

}