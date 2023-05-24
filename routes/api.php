<?php

use App\Http\Controllers\API\AddInventoryController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\PettyCashAllocationController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\SubmitAuditStockController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\UserController;
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

Route::post('auth/login', [AuthController::class, 'login'])->withoutMiddleware('permissions');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('/reports', [DashboardController::class, 'reports']);

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::patch('/{user}/', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    Route::group(['prefix' => 'add-inventory'], function () {
        Route::get('/', [AddInventoryController::class, 'index']);
        Route::post('/', [AddInventoryController::class, 'store']);
        Route::patch('/{addInventory}/approve', [AddInventoryController::class, 'approve']);
        Route::patch('/{addInventory}/reject', [AddInventoryController::class, 'reject']);
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
        Route::get('/', [CategoriesController::class, 'index']);
        Route::get('/{category}', [CategoriesController::class, 'show']);
        Route::post('/', [CategoriesController::class, 'store']);
        Route::patch('/{category}', [CategoriesController::class, 'update']);
        Route::delete('/{category}', [CategoriesController::class, 'destroy']);
    });

    Route::post('petty-cash-allocation', PettyCashAllocationController::class);

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'search']);
        Route::post('/', [ProductController::class, 'store']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });

    Route::group(['prefix' => 'sales'], function () {
        Route::get('/', [SaleController::class, 'index']);
    });

    Route::group(['prefix' => 'expenses'], function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::patch('/{expense}', [ExpenseController::class, 'update']);
        Route::delete('/{expense}', [ExpenseController::class, 'destroy']);
    });

    Route::group(['prefix' => 'mobile'], function () {
            Route::post('/add-inventory', [AddInventoryController::class, 'store']);
            Route::get('/suppliers', \App\Http\Controllers\Mobile\SupplierController::class);
            Route::get('/products', \App\Http\Controllers\Mobile\ProductController::class);
    });
});
