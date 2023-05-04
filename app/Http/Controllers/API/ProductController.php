<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AddInventory;
use App\Models\Product;
use App\Models\ProductStockHistory;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index(): LengthAwarePaginator
    {
        return Product::with("category")
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function search($product)
    {
        return Product::where('product_name','like','%'.$product.'%')
            ->with('category','sales')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function store(Request $request): Product
    {
        $request->validate([
            'product_photo' => 'required|image|mimes:jpeg,png,jpg|max:5000',
        ]);

        $imageName = $request->get('product_name') . '.' . $request->file('product_photo')->extension();

        $path = $request->file('product_photo')->move(public_path('/images/products'),$imageName);

        return Product::create([
            'product_name'=>$request->get('product_name'),
            'description'=>$request->get('description'),
            'price'=>$request->get('price'),
            'category_id'=>$request->get('category_id'),
            'product_photo_path' => $path
        ]);
    }

    public function destroy(Product $product): ?bool
    {
        return $product->delete();
    }
}
