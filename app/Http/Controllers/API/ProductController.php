<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AddInventory;
use App\Models\Product;
use App\Models\ProductStockHistory;
use App\Models\Sale;
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

    public function store(Request $request): Product
    {
        return Product::create([
            'product_name'=>$request->get('product_name'),
            'description'=>$request->get('description'),
            'price'=>$request->get('price'),
            'category_id'=>$request->get('category_id'),
            'product_photo_path' => $request->file('product_photo')->store('public/images/products')
        ]);
    }

    public function destroy($product): void
    {
        $product = Product::find($product);
        $product->delete();
    }
}
