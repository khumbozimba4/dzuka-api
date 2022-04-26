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

    public function search($name)
    {
        return Sale::where('customer_name','like','%'.$name.'%')->with("products")->get();
    }


    // SORT BY SALES MADE TODAY

    public function today()
    {
        return Sale::whereDate('date', Carbon::today())->with('products')->orderBy('created_at', 'desc')->get();
    }

    // SORT BY SALE DATE

    public function dateDesc()
    {
        return Sale::orderBy('date', 'desc')->with('products')->get();
    }
    public function dateAsc()
    {
        return Sale::orderBy('date', 'asc')->with('products')->get();
    }

    // SORT BY SALE AMOUNT

    public function amountDesc()
    {
        return Sale::orderBy('sale_amount', 'desc')->with('products')->get();
    }
    public function amountAsc()
    {
        return Sale::orderBy('sale_amount', 'asc')->with('products')->get();
    }

    // STORE

    public function store(Request $request)
    {
        $this->validate($request,[
            "date"=>"required",
            "customer_contact"=>"required",
            "customer_name"=>"required",
            "description"=>"required",
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
