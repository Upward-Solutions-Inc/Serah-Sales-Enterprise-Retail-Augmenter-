<?php

namespace App\Models\Retail\Ingredients;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'ingredient_name', 'measurement_type', 'unit', 'amount'
    ];
}