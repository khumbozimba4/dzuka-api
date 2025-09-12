<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'location' => 'sometimes',
            'pin' => 'required',
            'phone_number' => 'sometimes',
            'category_id'=>'required',
            'center_id'=>'required'
        ];
    }
}
