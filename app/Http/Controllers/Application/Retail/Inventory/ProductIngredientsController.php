<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Retail\Ingredients\Measurement;
use App\Models\Retail\Ingredients\Ingredient;

class ProductIngredientsController extends Controller
{

    public function index()
    {
        return view('custom.retail.inventory.product_ingredients');
    }

    public function fetchIngredients()
    {
        return response()->json(Ingredient::orderBy('id', 'desc')->get());
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'nullable|file|image|max:2048',
            'ingredient_name' => 'required|string',
            'brand' => 'nullable|string',
            'category' => 'nullable|string',
            'measurement_type' => 'required|string',
            'unit' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('ingredients', 'public');
            $validated['image'] = '/storage/' . $path;
        } else {
            $validated['image'] = '/storage/err/image.png';
        }

        $ingredient = Ingredient::create($validated);

        return response()->json($ingredient, 201);
    }

    public function show($id)
    {
        return response()->json(Ingredient::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'image' => 'nullable|file|image|max:2048',
            'ingredient_name' => 'required|string',
            'brand' => 'nullable|string',
            'category' => 'nullable|string',
            'measurement_type' => 'required|string',
            'unit' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('ingredients', 'public');
            $validated['image'] = '/storage/' . $path;
        }

        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update($validated);

        return response()->json($ingredient);
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        
        if ($ingredient->image) {
            $path = str_replace('/storage/', '', $ingredient->image);
            Storage::disk('public')->delete($path);
        }

        $ingredient->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}