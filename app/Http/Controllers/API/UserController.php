<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return response(User::with('role')
            ->orderBy('created_at', 'desc')
            ->paginate(10));
    }

    public function search($name)
    {
        return response(User::where('name', 'like', '%' . $name . '%')
            ->orWhere('email', 'like', '%' . $name . '%')
            ->paginate(10));
    }


    public function update(Request $request, User $user)
    {

        return \response($user->update([
            "name" => $request->get('name'),
            "email" => $request->get('email'),
            "role_id" => $request->get('role_id'),
            "category_id" => $request->get('category_id'),
        ]));
    }

    public function changePin(Request $request, User $user)
    {
        if (!Hash::check($request->get('password'), $user->{'password'})) {
            return \response(['message' => 'Old password is incorrect'], 401);
        }

        $user->{'password'} = Hash::make($request->get('new_password'));
        $user->save();
        return \response($user);
    }

    public function destroy(User $user)
    {
        return \response($user->delete());
    }
}
