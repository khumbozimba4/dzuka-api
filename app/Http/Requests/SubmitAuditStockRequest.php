<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAuditStockRequest extends FormRequest
{
    public function rules()
    {
        return [
            'product_id' => 'required',
            'stock_count' => 'required',
        ];
    }
}
