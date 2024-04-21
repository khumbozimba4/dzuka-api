<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PettyCashAllocationRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'amount' => 'required',
            'center_id' => 'required'
        ];
    }
}
