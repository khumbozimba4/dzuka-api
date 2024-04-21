<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PettyCashAllocationRequest;
use App\Models\Category;
use App\Models\Center;
use App\Models\PettyCashAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PettyCashAllocationController extends Controller
{
    public function __invoke(PettyCashAllocationRequest $request)
    {
        $center = Center::find($request->get('center_id'));
        $center->update(['petty_cash' => $center->{'petty_cash'} + $request->get('amount')]);
        return response(PettyCashAllocation::create(
            array_merge($request->validated(), ['user_id' => Auth::user()->{'id'}])
        ));
    }
}
