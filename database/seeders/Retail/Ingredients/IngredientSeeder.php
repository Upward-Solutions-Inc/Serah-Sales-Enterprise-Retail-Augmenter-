<?php

namespace Database\Seeders\Retail\Ingredients;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    public function run()
    {
        DB::table('ingredients')->insert([
            [
                'image' => 'sugar.png',
                'ingredient_name' => 'Sugar',
                'brand' => 'SweetCo',
                'category' => 'Sweetener',
                'measurement_type' => 'Weight',
                'unit' => 'g',
                'amount' => 500
            ],
            [
                'image' => 'milk.png',
                'ingredient_name' => 'Milk',
                'brand' => 'DairyBest',
                'category' => 'Dairy',
                'measurement_type' => 'Volume',
                'unit' => 'ml',
                'amount' => 1000
            ],
        ]);
    }
}