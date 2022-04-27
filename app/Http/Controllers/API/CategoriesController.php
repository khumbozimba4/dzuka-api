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

    public function search($name)
    {
        return Category::where('category_name','like','%'.$name.'%')->with('products')->get();
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            "category_name"=>"required",
            "description"=>"required",
        ]);
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
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $category -> update([
            "category_name" => $request->category_name,
            "description" => $request->description
        ]);
        return $category;
    }


    public function destroy(Category $category)
    {
        //
    }
}
