<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{

    public function index()
    {
        return response(Category::with('products')
            ->orderBy('created_at', 'desc')
            ->paginate(10));
    }

    public function search($name)
    {
        return response(
            Category::where('category_name', 'like', '%' . $name . '%')
                ->with('products')
                ->paginate(10)
        );
    }

    public function store(CategoryRequest $request)
    {
        return response(Category::create($request->validated()));
    }

    public function show(Category $category)
    {
        return response([
            'category' => $category,
            'products' => $category->{'products'}
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        return response($category->update($request->validated()));
    }

    public function destroy(Category $category)
    {
        return response($category->delete());
    }
}
