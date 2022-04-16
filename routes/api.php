<?php

use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories',[CategoriesController::class,'index']);
Route::get('/categories/{category}/products',[CategoriesController::class,'show']);
Route::post('/categories/store',[CategoriesController::class,'store']);

Route::get('/products',[ProductController::class,'index']);
Route::post('/products/store',[ProductController::class,'store']);
