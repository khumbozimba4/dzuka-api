<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PettyCashAllocationRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'amount' => 'required',
            'category_id' => 'required'
        ];
    }
}
