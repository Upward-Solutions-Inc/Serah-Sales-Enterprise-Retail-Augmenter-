<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Retail\Ingredients\Measurement;
use Illuminate\Http\Request;

class ProductMeasurementsController extends Controller
{

    public function index()
    {
        return view('custom.retail.inventory.product_measurements');
    }

    public function fetchList()
    {
        return response()->json(Measurement::orderBy('id', 'desc')->get());
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'unit' => 'required|string',
            'label' => 'required|string',
            'multiplier' => 'required|numeric',
            'base_unit' => 'required|string',
        ]);

        return response()->json(Measurement::create($validated), 201);
    }

    public function show($id)
    {
        return response()->json(Measurement::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'unit' => 'required|string',
            'label' => 'required|string',
            'multiplier' => 'required|numeric',
            'base_unit' => 'required|string',
        ]);

        $measurement = Measurement::findOrFail($id);
        $measurement->update($validated);

        return response()->json($measurement);
    }

    public function destroy($id)
    {
        Measurement::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully.']);
    }
}