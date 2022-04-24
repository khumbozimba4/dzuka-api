<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class  AuthController extends Controller
{
    
// LOGIN
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|string'
        ]);
        // Check email
        $user = User::where('email',$fields['email'])->first();
       //check password
        if(!$user||!Hash::check($fields['password'],$user->password)){
            return response([
                'message'=>'Incorrect Credentials'
            ],401);
        }
        $token = $user->createToken('myapptoken')->plainTextToken;
        $response = [
            'user'=>$user,
            'token'=>$token
        ];
        return response($response,201);

    }
// REGISTER
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users,email',
            'role'=>'required|string',
            'password'=>'required|string|confirmed'
        ]);
        $user = User::create([
            'name'=>$fields['name'],
            'email'=>$fields['email'],
            'role'=>$fields['role'],
            'password'=>bcrypt($fields['password']),
        ]);
        $token = $user->createToken('myapptoken')->plainTextToken;
        $response = [
            'user'=>$user,
            'token'=>$token
        ];
        return response($response,201);

    }
    
}
