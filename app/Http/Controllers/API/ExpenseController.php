<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return \response(Expense::orderBy('created_at', 'desc')->get());
    }

    public function search($name)
    {
        return Expense::where('expense_on','like','%'.$name.'%')->get();
    }

    public function store(ExpenseRequest $request)
    {
        return Expense::create($request->validated());
    }

    public function update(ExpenseRequest $request, Expense $expense): Expense
    {
        $expense->update($request->validated());
        return $expense;
    }

    public function destroy(Expense $expense): ?bool
    {
        return $expense->delete();
    }
}
