<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAuditStockRequest;
use App\Models\AddInventory;
use App\Models\Product;
use App\Models\ProductStockHistory;
use App\Models\Sale;
use App\Models\SubmitAuditStock;
use Illuminate\Http\Request;

class SubmitAuditStockController extends Controller
{
    public function index()
    {
        return response(SubmitAuditStock::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
        );
    }

    public function store(SubmitAuditStockRequest $request)
    {
        $product = Product::find($request->get('product_id'));

        SubmitAuditStock::create([
            'product_id' => $request->get('product_id'),
            'stock_count' => $request->get('stock_count'),
            'user_id' => auth()->id()
        ]);

        $sale_quantity = $product->{'stock'} - $request->get('stock_count');

        Sale::create([
            'product_id' => $request->get('product_id'),
            'amount' => $product->{'price'} * $sale_quantity,
            'quantity' => $sale_quantity
        ]);

        return response($product->update(['stock' => $request->get('stock_count')]));
    }
}
