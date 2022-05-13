<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::get();
    }

    public function search($name)
    {
        return User::where('name','like','%'.$name.'%')->orWhere('email','like','%'.$name.'%')->get();
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);
       
        $user->update([
            "name"=>$request->name,
            "email"=>$request->email,
            "role"=>$request->role,  
        ]);
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return;
    }
}
