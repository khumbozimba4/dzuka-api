<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
 
    public function index()
    {
        return Product::orderBy('created_at', 'desc')->get();
    }

    public function search($name)
    {
        return Product::where('product_name','like','%'.$name.'%')->with('category','sales')->get();
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

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $product->update([
            'stock'=>$request->stock,
            'recently_allocated'=>$request->recently_allocated,
            'previous_stock'=>$request->previous_stock
        ]);
        return $product;
    }
    public function subtract(Request $request, $id)
    {
        $product = Product::find($id);
        $product->update([
            'stock'=>$request->stock,
            'recently_subtracted'=>$request->recently_subtracted,
            'previous_stock'=>$request->previous_stock
        ]);
        return $product;
    }


    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return;
    }
}
