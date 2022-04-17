<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class ProductSaleController extends Controller
{

    public function store(Request $request, Sale $sale)
    {
        $sale->product()->attach($request->product_id,[
            'quantity'=>$request->quantity
        ]);
        return;
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
