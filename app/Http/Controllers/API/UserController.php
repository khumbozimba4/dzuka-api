<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return response(User::with('role')->orderBy('created_at', 'desc')->paginate(10));
    }

    public function search($name)
    {
        return User::where('name','like','%'.$name.'%')->orWhere('email','like','%'.$name.'%')->paginate(10);
    }


    public function update(Request $request, User $user): User
    {
        $user->update([
            "name"=>$request->get('name'),
            "email"=>$request->get('email'),
            "role_id"=>$request->get('role_id'),
        ]);
        return $user;
    }

    public function destroy(User $user): ?bool
    {
        return $user->delete();
    }
}
