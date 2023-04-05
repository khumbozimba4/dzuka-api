<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return Supplier::all();
    }

    public function store(SupplierRequest $request)
    {
        return Supplier::create($request->validated());
    }

    public function update(SupplierRequest $request, $supplier)
    {
       return Supplier::find($supplier)->update($request->validated());
    }

    public function destroy($supplier)
    {
       return Supplier::find($supplier)->delete();
    }
}
