<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddInventoryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'product_id' => 'required',
            'supplier_id' => 'required',
            'quantity' => 'required',
        ];
    }
}
