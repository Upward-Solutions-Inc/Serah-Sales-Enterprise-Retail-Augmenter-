<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;

class ProductRecipeController extends Controller
{

    public function index()
    {
        return view('custom.retail.inventory.product_recipe');
    }
}