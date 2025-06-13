<?php

namespace Database\Seeders\Retail\Ingredients;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    public function run()
    {
        DB::table('ingredients')->insert([
            ['ingredient_name' => 'Sugar', 'measurement_type' => 'Weight', 'unit' => 'g', 'amount' => 500],
            ['ingredient_name' => 'Milk', 'measurement_type' => 'Volume', 'unit' => 'ml', 'amount' => 1000],
        ]);
    }
}