<?php

namespace App\Http\Requests;

use Bluecloud\ResponseBuilder\Requests\BaseFormRequest;

class CategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            "category_name"=>"required",
        ];
    }
}
