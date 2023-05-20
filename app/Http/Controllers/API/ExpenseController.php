<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
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
        return \response(Expense::with('category')->orderBy('created_at', 'desc')->paginate(10));
    }

    public function search($name)
    {
        return \response(Expense::where('expense_on', 'like', '%' . $name . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10));
    }

    public function store(ExpenseRequest $request)
    {
        $category = Category::find($request->get('category_id'));
        $category->update([
            'petty_cash'=>$category->{'petty_cash'}-$request->get('amount')
        ]);
        return \response(Expense::create($request->validated()));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        return \response($expense->update($request->validated()));
    }

    public function destroy(Expense $expense)
    {
        return \response($expense->delete());
    }
}
