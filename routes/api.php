<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductSaleController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
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

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware'=>['auth:sanctum']],function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/search/{name}', [UserController::class, 'search']);
    Route::patch('/users/{id}/update', [UserController::class, 'update']);
    Route::delete('/users/{id}/destroy', [UserController::class, 'destroy']);

    Route::get('/categories',[CategoriesController::class,'index']);
    Route::get('/categories/search/{name}',[CategoriesController::class,'search']);
    Route::get('/categories/{category}/products',[CategoriesController::class,'show']);
    Route::post('/categories/store',[CategoriesController::class,'store']);
    Route::patch('/categories/{id}/update',[CategoriesController::class,'update']);
    Route::delete('/categories/{id}/destroy',[CategoriesController::class,'destroy']);

    Route::get('/products',[ProductController::class,'index']);
    Route::get('/{product}/show',[ProductController::class,'show']);
    Route::get('/products/search/{name}',[ProductController::class,'search']);
    Route::post('/products/store',[ProductController::class,'store']);
    Route::patch('/products/{id}/update',[ProductController::class,'update']);
    Route::patch('/products/{id}/inventory/subtract',[ProductController::class,'subtract']);
    Route::delete('/products/{id}/destroy',[ProductController::class,'destroy']);


    Route::get('/sales',[SaleController::class,'index']);
    Route::get('/sales/{sale}/show',[SaleController::class,'show']);
    Route::get('/sales/search/{name}',[SaleController::class,'search']);
    Route::get('/sales/today',[SaleController::class,'today']);
    Route::get('/sales/sort/amount/desc',[SaleController::class,'amountDesc']);
    Route::get('/sales/sort/amount/asc',[SaleController::class,'amountAsc']);
    Route::get('/sales/sort/date/desc',[SaleController::class,'dateDesc']);
    Route::get('/sales/sort/date/asc',[SaleController::class,'dateAsc']);
    Route::post('/sales/store',[SaleController::class,'store']);
    Route::patch('/sales/{sale}/amount/update',[SaleController::class,'amount']);
    Route::patch('/sales/{sale}/update',[SaleController::class,'update']);
    Route::delete('/sales/{sale}/destroy',[SaleController::class,'destroy']);

    Route::get('/expenses',[ExpenseController::class,'index']);
    Route::get('/expenses/search/{name}',[ExpenseController::class,'search']);
    Route::get('/expenses/today',[ExpenseController::class,'today']);
    Route::post('/expenses/store',[ExpenseController::class,'store']);
    Route::patch('/expenses/{id}/update',[ExpenseController::class,'update']);
    Route::delete('/expenses/{id}/destroy',[ExpenseController::class,'destroy']);

    Route::get('/transactions',[TransactionController::class,'index']);
    Route::get('/transactions/search/{name}',[TransactionController::class,'search']);
    Route::get('/transactions/today',[TransactionController::class,'today']);
    Route::post('/transactions/store',[TransactionController::class,'store']);

    Route::post('/sales/product/{product}/store',[ProductSaleController::class,'store']);
    Route::delete('/sales/product/{product}/destroy',[ProductSaleController::class,'destroy']);
    Route::get('/sales/{sale}/products',[ProductSaleController::class,'index']);

    Route::get('/user',function(Request $request){
        return $request->user();
    });
});
