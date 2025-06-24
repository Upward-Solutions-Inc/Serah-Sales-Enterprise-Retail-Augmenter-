<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Retail\Ingredients\Ingredient;
use App\Models\Pos\Product\Product\Product;
use Illuminate\Support\Facades\DB;

class ProductRecipeController extends Controller
{

    public function index()
    {
        return view('custom.retail.inventory.product_recipe');
    }

    public function fetchDropdowns()
    {
        $products = Product::active()
            ->select('id', 'name', 'category_id')
            ->with('category:id,name')
            ->get();

        $categories = $products
            ->pluck('category')
            ->unique('id')
            ->values()
            ->filter();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function fetchIngredients()
    {
        $ingredients = Ingredient::select('id', 'ingredient_name as name', 'unit', 'image')->get();
        return response()->json($ingredients);
    }
}