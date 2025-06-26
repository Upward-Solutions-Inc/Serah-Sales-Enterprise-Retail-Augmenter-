<?php

namespace Database\Seeders\Retail\Recipe;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeIngredientSeeder extends Seeder
{
    public function run()
    {
        // Example: Insert ingredients for recipe_id 1
        DB::table('recipe_ingredients')->insert([
            [
                'recipe_id' => 2,
                'ingredient_id' => 6,
                'amount' => 1,
                'unit' => 'pcs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'recipe_id' => 2,
                'ingredient_id' => 7,
                'amount' => 1,
                'unit' => 'ml',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more recipe ingredients as needed
        ]);
    }
}
