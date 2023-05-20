<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "date" => "required",
            "expense_on" => "required",
            "amount" => "required",
            "description" => "sometimes",
            "category_id" => "required",
        ];
    }
}
