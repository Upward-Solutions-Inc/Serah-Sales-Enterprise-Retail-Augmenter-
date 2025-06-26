<?php

namespace Database\Seeders\Retail\Recipe;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    public function run()
    {
        // Example: Insert a recipe for product_id 1, category_id 1
        DB::table('recipes')->insert([
            [
                'product_id' => 3,
                'category_id' => 2,
                'name' => 'Sample Recipe',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more recipes as needed
        ]);
    }
}
