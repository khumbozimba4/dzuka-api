<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Sale::with('products')->orderBy('date', 'desc')->get();
    }
    public function today()
    {
        return Sale::whereDate('date', Carbon::today())->with('products')->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            "date"=>"required",
            "customer_contact"=>"required",
        ]);

        $sale=new Sale();
        $sale->customer_name=$request->customer_name;
        $sale->customer_contact=$request->customer_contact;
        $sale->date=$request->date;
        $sale->description=$request->description;
        $sale->save();
        return $sale;
    }


    public function show(Sale $sale)
    {
        //
    }

   
    public function amount(Request $request, $sale)
    {
        $sale = Sale::find($sale);
        $sale_amount = $sale->sale_amount;
        $sale->update([
            'sale_amount'=>($request->amount) + $sale_amount
        ]);
        return $sale;
    }
    public function update(Request $request,  $sale)
    {
      
    }

   
    public function destroy(Sale $sale)
    {
        //
    }
}
