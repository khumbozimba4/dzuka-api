<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStockHistory;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        return Product::with('histories.product')->orderBy('created_at', 'desc')->get();
    }

    public function search($name)
    {
        return Product::where('product_name','like','%'.$name.'%')->with('category','sales')->get();
    }

    public function show($product)
    {
        return Product::where('id','=',$product)->with('histories')->first();
    }

    public function zero()
    {
        return Product::where('stock',"=",0)->orderBy('created_at', 'desc')->get();
    }



    public function store(Request $request)
    {
        $this->validate($request,[
            "product_name"=>"required",
            "price"=>"required",
            "category_id"=>"required",
            "description"=>"required",
        ]);

        $product=new Product();
        $product->product_name=$request->product_name;
        $product->price=$request->price;
        $product->description=$request->description;
        $product->category_id=$request->category_id;
        $product->save();
        return $product;
    }

    public function update(Request $request, $product)
    {
        $product = Product::find($product);

        ProductStockHistory::create([
            'product_id' => $product->getKey(),
            'stock_count'=>$request->get('stock_count'),
            'previous_stock_count'=> $product->{'stock'},
            'submitted_by'=>auth()->user()->{'name'}
        ]);

        return $product->update(['stock' => $request->get('stock_count')]);
    }

    public function destroy(Product $product): void
    {
       $product->delete();
    }
}
