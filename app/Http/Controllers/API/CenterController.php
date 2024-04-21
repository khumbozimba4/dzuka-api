<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterRequest;
use App\Models\Center;
use Illuminate\Http\JsonResponse;

class CenterController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->respond()->key('centers')->ok(Center::useFilter()->paginate(10))->toJson();
    }

    public function show(Center $center): JsonResponse
    {
        return $this->respond()->key('center')->ok($center->loadRelations()->toArray())->toJson();
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
