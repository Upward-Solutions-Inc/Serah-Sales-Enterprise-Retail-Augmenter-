<?php

namespace Database\Seeders\Retail\Ingredients;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasurementsSeeder extends Seeder
{
    public function run()
    {
        DB::table('measurements')->insert([
            // Volume
            ['type' => 'volume', 'unit' => 'ml', 'label' => 'Milliliter', 'multiplier' => 1, 'base_unit' => 'ml', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'volume', 'unit' => 'L',  'label' => 'Liter',      'multiplier' => 1000, 'base_unit' => 'ml', 'created_at' => now(), 'updated_at' => now()],

            // Mass
            ['type' => 'mass', 'unit' => 'g',  'label' => 'Gram',     'multiplier' => 1, 'base_unit' => 'g', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'mass', 'unit' => 'kg', 'label' => 'Kilogram', 'multiplier' => 1000, 'base_unit' => 'g', 'created_at' => now(), 'updated_at' => now()],

            // Length
            ['type' => 'length', 'unit' => 'mm', 'label' => 'Millimeter', 'multiplier' => 1, 'base_unit' => 'mm',  'created_at' => now(), 'updated_at' => now()],
            ['type' => 'length', 'unit' => 'cm',  'label' => 'Centimeter',  'multiplier' => 10,  'base_unit' => 'mm', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'length', 'unit' => 'm',  'label' => 'Meter',      'multiplier' => 1000, 'base_unit' => 'mm' , 'created_at' => now(), 'updated_at' => now()],

            // Count
            ['type' => 'count', 'unit' => 'pcs', 'label' => 'Pieces', 'multiplier' => 1, 'base_unit' => 'pcs' , 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
