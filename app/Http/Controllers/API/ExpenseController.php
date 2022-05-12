<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Expense::orderBy('created_at', 'desc')->get();
    }

    public function search($name)
    {
        return Expense::where('expense_on','like','%'.$name.'%')->get();
    }


    public function store(Request $request)
    {
        $this->validate($request,[
            "date"=>"required",
            "expense_on"=>"required",
            "amount"=>"required",
            "description"=>"required",
        ]);

        $expense=new Expense();
        $expense->date=$request->date;
        $expense->expense_on=$request->expense_on;
        $expense->amount=$request->amount;
        $expense->description=$request->description;
        $expense->save();
        return $expense;
    }


    public function update(Request $request, $expense)
    {
        $expense = Expense::find($expense);
       
        $expense->update([
            "expense_on"=>$request->expense_on,
            "date"=>$request->date,
            "amount"=>$request->amount,  
            "description"=>$request->description,  
        ]);
        return $expense;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy($expense)
    {
        $expense = Expense::find($expense);
        $expense->delete();
        return;
    }
}
