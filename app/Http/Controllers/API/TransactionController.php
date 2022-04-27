<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Transaction::with('user')->orderBy('created_at', 'desc')->get();
    }

    public function search($name)
    {
        return Transaction::where('user_id','like','%'.$name.'%')->orWhere('transaction_name','like','%'.$name.'%')->with('user')->get();
    }

    public function today()
    {
        return Transaction::whereDate('created_at', Carbon::today())->with('user')->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'user_id'=>'required',
            'transaction_name'=>'required',
            'description'=>'required',
        ]);

        $transaction = new Transaction();
        $transaction -> user_id = $request -> user_id;
        $transaction -> description = $request -> description;
        $transaction -> transaction_name = $request -> transaction_name;
        $transaction -> save();
        return $transaction;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
