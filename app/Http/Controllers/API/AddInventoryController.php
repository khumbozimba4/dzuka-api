<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddInventoryRequest;
use App\Models\AddInventory;
use App\Models\Product;
use Illuminate\Http\Request;

class AddInventoryController extends Controller
{
    public function index()
    {
        return response(AddInventory::with('product', 'supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(2));
    }

    public function store(AddInventoryRequest $request)
    {
        $product = Product::find($request->get('product_id'));
        $product->update([
            'stock' => $request->get('quantity') + $product->{'stock'}
        ]);
        return response(AddInventory::create($request->validated()));
    }
}
