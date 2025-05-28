<?php

namespace App\Http\Controllers\Application\Retail\Inventory;

use App\Http\Controllers\Controller;

class IngredientsController extends Controller
{

    public function index()
    {
        return view('custom.retail.inventory.ingredients');
    }
}