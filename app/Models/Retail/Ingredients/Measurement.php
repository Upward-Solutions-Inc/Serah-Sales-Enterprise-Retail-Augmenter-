<?php

namespace App\Models\Retail\Ingredients;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    protected $fillable = [
        'type', 'unit', 'label', 'multiplier', 'base_unit'
    ];
}
