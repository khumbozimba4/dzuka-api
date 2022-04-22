<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductSaleController;
use App\Http\Controllers\API\SaleController;
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
Route::get('/products/search/{name}',[ProductController::class,'search']);
Route::post('/products/store',[ProductController::class,'store']);
Route::patch('/products/{id}/update',[ProductController::class,'update']);
Route::patch('/products/{id}/inventory/subtract',[ProductController::class,'subtract']);

Route::get('/sales',[SaleController::class,'index']);
Route::get('/sales/today',[SaleController::class,'today']);
Route::post('/sales/store',[SaleController::class,'store']);
Route::patch('/sales/{sale}/amount/update',[SaleController::class,'amount']);
Route::patch('/sales/{sale}/update',[SaleController::class,'update']);

Route::get('/expenses',[ExpenseController::class,'index']);
Route::get('/expenses/today',[ExpenseController::class,'today']);
Route::post('/expenses/store',[ExpenseController::class,'store']);


Route::post('/sales/product/{product}/store',[ProductSaleController::class,'store']);
Route::get('/sales/{sale}/products',[ProductSaleController::class,'index']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware'=>['auth:sanctum']],function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',function(Request $request){
        return $request->user();
    });
});
