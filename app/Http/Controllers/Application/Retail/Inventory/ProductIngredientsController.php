<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Retail\Ingredients\Measurement;

class ProductIngredientsController extends Controller
{

    public function index()
    {
        return view('custom.retail.inventory.product_ingredients');
    }

    public function fetchMeasurements()
    {
        $types = Measurement::select('type', 'base_unit')
            ->distinct()
            ->get()
            ->map(function ($type) {
                $type->units = Measurement::where('type', $type->type)
                    ->select('unit', 'label', 'multiplier')
                    ->get();
                return $type;
            });

        return response()->json($types);
    }
}