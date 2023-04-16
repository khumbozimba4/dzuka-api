<?php

use App\Http\Controllers\API\AddInventoryController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\SubmitAuditStockController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use App\Http\Middleware\Footprints;
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

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/search/{name}', [UserController::class, 'search']);
        Route::patch('/{user}/', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    Route::group(['prefix' => 'add-inventory'], function () {
        Route::get('/', [AddInventoryController::class, 'index']);
        Route::post('/', [AddInventoryController::class, 'store']);
    });

    Route::group(['prefix' => 'submit-audit-stock'], function () {
        Route::get('/', [SubmitAuditStockController::class, 'index']);
        Route::post('/', [SubmitAuditStockController::class, 'store']);
    });

    Route::group(['prefix' => 'suppliers'], function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::patch('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/',[CategoriesController::class,'index']);
        Route::get('/{name}/search',[CategoriesController::class,'search']);
        Route::get('/{category}',[CategoriesController::class,'show']);
        Route::post('/',[CategoriesController::class,'store']);
        Route::patch('/{category}',[CategoriesController::class,'update']);
        Route::delete('/{category}',[CategoriesController::class,'destroy']);
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('/',[ProductController::class,'index']);
        Route::get('/search/{name}',[ProductController::class,'search']);
        Route::post('/store',[ProductController::class,'store']);
        Route::delete('/{product}/destroy',[ProductController::class,'destroy']);
    });

    Route::group(['prefix' => 'sales'], function () {
        Route::get('/',[SaleController::class,'index']);
    });

    Route::group(['prefix' => 'expenses'], function () {
        Route::get('/',[ExpenseController::class,'index']);
        Route::get('/search/{name}',[ExpenseController::class,'search']);
        Route::get('/today',[ExpenseController::class,'today']);
        Route::post('/store',[ExpenseController::class,'store']);
        Route::patch('/{expense}/update',[ExpenseController::class,'update']);
        Route::delete('/{expense}/destroy',[ExpenseController::class,'destroy']);
    });

    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/',[TransactionController::class,'index']);
        Route::get('/search/{name}',[TransactionController::class,'search']);
        Route::get('/today',[TransactionController::class,'today']);
        Route::post('/store',[TransactionController::class,'store']);
    });

    Route::get('/user',function(Request $request){
        return $request->user();
    });
});
