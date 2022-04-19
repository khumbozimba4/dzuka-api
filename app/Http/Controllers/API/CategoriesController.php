<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{

    public function index()
    {
        return Category::with("products")->orderBy('created_at', 'desc')->get();
        
    }

    public function store(Request $request)
    {
        $category=new Category();
        $category->category_name=$request->category_name;
        $category->description=$request->description;
        $category->save();
        return $category;
    }


    public function show(Category $category)
    {
        return $category->products;
    }
    public function update(Request $request, Category $category)
    {
        //
    }


    public function destroy(Category $category)
    {
        //
    }
}
