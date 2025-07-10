<?php

namespace App\Services\Retail;

use App\Models\Retail\Recipe\Recipe;
use App\Models\Retail\Recipe\RecipeIngredient;
use App\Models\Retail\Ingredients\Ingredient;
use App\Models\Pos\Product\Product\Product;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling Recipe, Ingredient, and Product stock logic in food retail.
 */
class RecipeService
{
    /**
     * Validate if a recipe can be created/updated based on available ingredient stocks.
     *
     * @param int $productId
     * @param array $ingredients Array of ['ingredient_id' => int, 'amount' => float, 'unit' => string]
     * @return array [ 'success' => bool, 'insufficient' => array, 'message' => string|null ]
     */
    public function validateRecipe($productId, array $ingredients)
    {
        $insufficient = [];
        foreach ($ingredients as $ing) {
            $ingredient = Ingredient::find($ing['ingredient_id']);
            if (!$ingredient) {
                $insufficient[] = [
                    'ingredient_id' => $ing['ingredient_id'],
                    'reason' => 'Ingredient not found.'
                ];
                continue;
            }
            if ($ingredient->amount < $ing['amount']) {
                $insufficient[] = [
                    'ingredient_id' => $ingredient->id,
                    'name' => $ingredient->name ?? $ingredient->ingredient_name,
                    'available' => $ingredient->amount,
                    'required' => $ing['amount'],
                    'reason' => 'Insufficient stock.'
                ];
            }
        }
        return [
            'success' => count($insufficient) === 0,
            'insufficient' => $insufficient,
            'message' => count($insufficient) ? 'Some ingredients have insufficient stock.' : null
        ];
    }

    /**
     * Restock a product by producing it from its recipe, deducting ingredient stocks.
     *
     * @param int $productId
     * @param int $quantity Number of products to produce
     * @return array [ 'success' => bool, 'message' => string, 'insufficient' => array ]
     */
    public function restockProduct($productId, $quantity)
    {
        $product = Product::find($productId);
        if (!$product) {
            return [ 'success' => false, 'message' => 'Product not found.', 'insufficient' => [] ];
        }
        $recipe = Recipe::where('product_id', $productId)->with('ingredients')->first();
        if (!$recipe) {
            return [ 'success' => false, 'message' => 'Recipe not found for this product.', 'insufficient' => [] ];
        }
        $insufficient = [];
        // Check all ingredients
        foreach ($recipe->ingredients as $ri) {
            $ingredient = Ingredient::find($ri->ingredient_id);
            $required = $ri->amount * $quantity;
            if (!$ingredient || $ingredient->amount < $required) {
                $insufficient[] = [
                    'ingredient_id' => $ri->ingredient_id,
                    'name' => $ingredient ? ($ingredient->name ?? $ingredient->ingredient_name) : 'Unknown',
                    'available' => $ingredient ? $ingredient->amount : 0,
                    'required' => $required,
                    'reason' => !$ingredient ? 'Ingredient not found.' : 'Insufficient stock.'
                ];
            }
        }
        if (count($insufficient)) {
            return [ 'success' => false, 'message' => 'Insufficient ingredients for restocking.', 'insufficient' => $insufficient ];
        }
        // Deduct ingredients and add to product stock
        DB::beginTransaction();
        try {
            foreach ($recipe->ingredients as $ri) {
                $ingredient = Ingredient::find($ri->ingredient_id);
                $ingredient->amount -= $ri->amount * $quantity;
                $ingredient->save();
            }
            $product->amount = ($product->amount ?? 0) + $quantity;
            $product->save();
            DB::commit();
            return [ 'success' => true, 'message' => 'Product restocked successfully.', 'insufficient' => [] ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [ 'success' => false, 'message' => $e->getMessage(), 'insufficient' => [] ];
        }
    }

    /**
     * Allocate (deduct) ingredient stocks when a recipe is created.
     *
     * @param array $ingredients Array of ['ingredient_id' => int, 'amount' => float]
     * @throws \Exception if any ingredient is insufficient
     */
    public function allocateIngredientsForRecipe(array $ingredients)
    {
        foreach ($ingredients as $ing) {
            $ingredient = Ingredient::find($ing['ingredient_id']);
            if (!$ingredient) {
                throw new \Exception('Ingredient not found: ' . $ing['ingredient_id']);
            }
            if ($ingredient->amount < $ing['amount']) {
                throw new \Exception('Insufficient stock for ingredient: ' . ($ingredient->name ?? $ingredient->ingredient_name));
            }
            $ingredient->amount -= $ing['amount'];
            $ingredient->save();
        }
    }

    /**
     * Restore ingredient stocks (used before updating a recipe).
     *
     * @param array $ingredients Array of ['ingredient_id' => int, 'amount' => float]
     */
    public function restoreIngredientsForRecipe(array $ingredients)
    {
        foreach ($ingredients as $ing) {
            $ingredient = Ingredient::find($ing['ingredient_id']);
            if ($ingredient) {
                $ingredient->amount += $ing['amount'];
                $ingredient->save();
            }
        }
    }

    /**
     * Deduct ingredients when a product is sold via POS.
     *
     * @param int $productId
     * @param int $quantity
     * @return array [ 'success' => bool, 'message' => string, 'insufficient' => array ]
     */
    public function deductIngredientsOnSale($productId, $quantity)
    {
        $recipe = Recipe::where('product_id', $productId)->with('ingredients')->first();
        if (!$recipe) {
            return [ 'success' => false, 'message' => 'No recipe for this product.', 'insufficient' => [] ];
        }
        $insufficient = [];
        foreach ($recipe->ingredients as $ri) {
            $ingredient = Ingredient::find($ri->ingredient_id);
            $required = $ri->amount * $quantity;
            if (!$ingredient || $ingredient->amount < $required) {
                $insufficient[] = [
                    'ingredient_id' => $ri->ingredient_id,
                    'name' => $ingredient ? ($ingredient->name ?? $ingredient->ingredient_name) : 'Unknown',
                    'available' => $ingredient ? $ingredient->amount : 0,
                    'required' => $required,
                    'reason' => !$ingredient ? 'Ingredient not found.' : 'Insufficient stock.'
                ];
            }
        }
        if (count($insufficient)) {
            return [ 'success' => false, 'message' => 'Insufficient ingredients for sale.', 'insufficient' => $insufficient ];
        }
        // Deduct ingredients
        DB::beginTransaction();
        try {
            foreach ($recipe->ingredients as $ri) {
                $ingredient = Ingredient::find($ri->ingredient_id);
                $ingredient->amount -= $ri->amount * $quantity;
                $ingredient->save();
            }
            DB::commit();
            return [ 'success' => true, 'message' => 'Ingredients deducted successfully.', 'insufficient' => [] ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [ 'success' => false, 'message' => $e->getMessage(), 'insufficient' => [] ];
        }
    }

    /**
     * Get the maximum number of products that can be produced based on available ingredient stocks.
     *
     * @param int $productId
     * @return int
     */
    public function getMaxProducibleQuantity($productId)
    {
        $recipe = Recipe::where('product_id', $productId)->with('ingredients')->first();
        if (!$recipe || $recipe->ingredients->isEmpty()) {
            return 0;
        }
        $maxQty = PHP_INT_MAX;
        foreach ($recipe->ingredients as $ri) {
            $ingredient = Ingredient::find($ri->ingredient_id);
            if (!$ingredient || $ri->amount <= 0) {
                return 0;
            }
            $possible = floor($ingredient->amount / $ri->amount);
            if ($possible < $maxQty) {
                $maxQty = $possible;
            }
        }
        return $maxQty;
    }
}
