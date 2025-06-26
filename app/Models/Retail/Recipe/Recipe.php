<?php

namespace App\Models\Retail\Recipe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'category_id',
        'name',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class)->withDefault();
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class)->withDefault();
    }

    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
