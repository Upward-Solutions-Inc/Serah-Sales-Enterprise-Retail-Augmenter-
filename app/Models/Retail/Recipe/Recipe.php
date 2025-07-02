<?php

namespace App\Models\Retail\Recipe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pos\Product\Product\Product;
use App\Models\Pos\Product\Category;
use App\Models\Retail\Recipe\RecipeIngredient;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'category_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
