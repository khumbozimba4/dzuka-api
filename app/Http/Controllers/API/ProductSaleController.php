<?php

namespace App\Http\Controllers\API;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;

class ProductSaleController extends Controller
{

    public function store(Request $request, $sale)

    {
        Debugbar::info($sale);
        $sale = Sale::find($sale);
        Debugbar::info($sale);
        $sale->products()->attach($request->product_id,[
            'quantity'=>$request->quantity
        ]);
        return $sale;
    }

    public function update(Request $request, Sale $sale)
    {
        //
    }

    public function destroy(Sale $sale)
    {
        //
    }
}
