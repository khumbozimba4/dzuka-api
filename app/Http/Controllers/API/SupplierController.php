<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        return response(Supplier::with('category')->orderBy('created_at', 'desc')->paginate(10));
    }

    public function getAll()
    {
        return response(Supplier::all());
    }

    public function store(SupplierRequest $request)
    {
        return response(Supplier::create($request->validated()));
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
       return response($supplier->update($request->validated())) ;
    }

    public function destroy(Supplier $supplier)
    {
       return response($supplier->delete());
    }
}
