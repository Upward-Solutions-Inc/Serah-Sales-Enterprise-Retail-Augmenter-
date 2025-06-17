<?php

namespace App\Models\Retail\Ingredients;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
    'image',
    'ingredient_name',
    'brand',
    'category',
    'measurement_type',
    'unit',
    'amount'
    ];
}