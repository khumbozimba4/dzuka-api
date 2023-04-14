<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class  AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->get('email'))->first();
        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return response([
                'message' => 'Incorrect Credentials'
            ], 401);
        }
        $token = $user->createToken($user->{'name'})->plainTextToken;
        $response = [
            'user' => array_merge($user->toArray(), [
                'role' => $user->role->{'name'}
            ]),
            'token' => $token
        ];
        return response($response, 200);

    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'role' => $request->get('role_id'),
            'password' => bcrypt($request->get('password'),),
        ]);
        $token = $user->createToken($request->get('name'))->plainTextToken;
        $response = [
            'user' => array_merge($user->toArray(), [
                'role' => $user->role->{'name'}
            ]),
            'token' => $token
        ];
        return response($response, 200);
    }

}
