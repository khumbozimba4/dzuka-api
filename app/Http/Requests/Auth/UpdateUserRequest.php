<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users,email',
            'role_id'=>'required',
            'center_id'=>'sometimes',
            'password'=>'sometimes|string'
        ];
    }
}
