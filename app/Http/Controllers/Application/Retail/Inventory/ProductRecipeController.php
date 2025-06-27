<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Retail\Ingredients\Ingredient;
use App\Models\Pos\Product\Product\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Retail\Recipe\Recipe;
use App\Models\Retail\Recipe\RecipeIngredient;
use App\Models\Pos\Product\Category\Category;

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
        $ingredients = Ingredient::select('id', 'ingredient_name as name', 'unit', 'image', 'amount')->get();
        return response()->json($ingredients);
    }

    public function fetchProducts()
    {
        $products = Product::active()->select('id', 'name')->get();
        return response()->json(['products' => $products]);
    }

    public function show($id)
    {
        $recipe = Recipe::with('ingredients')->findOrFail($id);
        return response()->json($recipe);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.amount' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'required|string',
        ]);
        DB::beginTransaction();
        try {
            $recipe = Recipe::create([
                'product_id' => $validated['product_id'],
                'category_id' => $validated['category_id'] ?? null,
            ]);
            \Log::info('Recipe created:', $recipe ? $recipe->toArray() : ['null']);
            foreach ($validated['ingredients'] as $ing) {
                $recipe->ingredients()->create([
                    'ingredient_id' => $ing['ingredient_id'],
                    'amount' => $ing['amount'],
                    'unit' => $ing['unit'],
                ]);
            }
            DB::commit();
            return response()->json(['success' => true, 'recipe' => $recipe->load('ingredients')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.amount' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'required|string',
        ]);
        DB::beginTransaction();
        try {
            $recipe = Recipe::findOrFail($id);
            $recipe->update(['product_id' => $validated['product_id']]);
            $recipe->ingredients()->delete();
            foreach ($validated['ingredients'] as $ing) {
                $recipe->ingredients()->create([
                    'ingredient_id' => $ing['ingredient_id'],
                    'amount' => $ing['amount'],
                    'unit' => $ing['unit'],
                ]);
            }
            DB::commit();
            return response()->json(['success' => true, 'recipe' => $recipe->load('ingredients')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->ingredients()->delete();
        $recipe->delete();
        return response()->json(['success' => true]);
    }

    public function fetchAllRecipes()
    {
        $recipes = Recipe::with(['ingredients', 'product:id,name'])
            ->get()
            ->map(function ($recipe) {
                return [
                    'id' => $recipe->id,
                    'name' => $recipe->product->name ?? '', // Add 'name' for frontend
                    'product_name' => $recipe->product->name ?? '',
                    'ingredients' => $recipe->ingredients,
                ];
            });
        return response()->json(['recipes' => $recipes]);
    }
}