<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $center = $user->role_id == 2 ? $user->center->{'name'} : null;
        $response = [
            'user' => array_merge($user->toArray(), [
                'role' => $user->role->{'name'},
                'center' => $center
            ]),
            'token' => $token
        ];
        return response($response, 200);

    }

    public function register(UpdateUserRequest $request)
    {
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'role_id' => $request->get('role_id'),
            'center_id' => $request->get('center_id'),
            'password' => bcrypt($request->get('password'),),
        ]);

        return response($user, 200);
    }

    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();
        
        return response([
            'message' => 'Successfully logged out'
        ], 200);
    }

}
