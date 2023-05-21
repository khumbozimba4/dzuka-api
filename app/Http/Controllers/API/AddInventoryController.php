<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddInventoryRequest;
use App\Models\AddInventory;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddInventoryController extends Controller
{
    public function index()
    {
        return response(AddInventory::with('product', 'supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(10));
    }

    public function store(AddInventoryRequest $request)
    {
        return response(AddInventory::create($request->validated()));
    }

    public function approve(AddInventory $addInventory)
    {
        $product = $addInventory->{'product'};
        $product->update([
            'stock' => $addInventory->{'quantity'} + $product->{'stock'}
        ]);
        $addInventory->{'approved_at'} = Carbon::now();
        $addInventory->{'approved_by'} = Auth::user()->{'id'};
        $addInventory->save();

        return response($addInventory);
    }

    public function reject(AddInventory $addInventory)
    {
        $addInventory->{'rejected_at'} = Carbon::now();
        $addInventory->{'rejected_by'} = Auth::user()->{'id'};
        $addInventory->save();

        return response($addInventory);
    }
}
