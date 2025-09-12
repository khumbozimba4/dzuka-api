<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterRequest;
use App\Models\Center;
use Illuminate\Http\JsonResponse;

class CenterController extends Controller
{
    public function index()
    {
        return $this->respond()->key('centers')->ok(Center::useFilter()->paginate(10))->toJson();
    }

    public function getAllCentres()
    {
        return response(Center::orderBy('created_at', 'desc')->get());
    }

    public function show(Center $center)
    {
        return $this->respond()
            ->key('center')
            ->ok($center->toArray()) // No relations loaded
            ->toJson();
    }
    
    public function store(CenterRequest $request)
    {
        return response(Center::create($request->validated()));
    }

    public function update(CenterRequest $request, Center $center)
    {
        return response($center->update($request->validated())) ;
    }

    public function destroy(Center $center)
    {
        return response($center->delete());
    }
}
