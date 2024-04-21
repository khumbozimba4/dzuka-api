<?php

namespace App\Http\Requests;

use Bluecloud\ResponseBuilder\Requests\BaseFormRequest;

class CenterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            "name"=>"required",
        ];
    }
}
