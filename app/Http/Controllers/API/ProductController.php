<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
 
    public function index()
    {
        return Product::get();
    }
  
    public function store(Request $request)
    {
        $this->validate($request,[
            "product_name"=>"required",
            "price"=>"required",
            "measurement"=>"required",

            "category_id"=>"required",
            "description"=>"required",
        ]);

        $product=new Product();
        $product->product_name=$request->product_name;
        $product->price=$request->price;
        $product->measurement=$request->measurement;
        $product->description=$request->description;
        $product->category_id=$request->category_id;
        $product->save();
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        //
    }


    public function destroy(Product $product)
    {
        //
    }
}
