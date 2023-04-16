<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{

    public function index()
    {
        return Category::with("products")->orderBy('created_at', 'desc')->get();
    }

    public function search($name)
    {
        return Category::where('category_name','like','%'.$name.'%')->with('products')->get();
    }

    public function store(CategoryRequest $request)
    {
        return Category::create($request->validated());
    }

    public function show(Category $category)
    {
        return $category->{'products'};
    }

    public function update(CategoryRequest $request, Category $category): bool
    {
        return $category -> update($request->validated());
    }

    public function destroy(Category $category): ?bool
    {
        return $category->delete();
    }
}
