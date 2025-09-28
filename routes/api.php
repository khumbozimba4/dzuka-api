<?php

use App\Http\Controllers\API\AddInventoryController;
use App\Http\Controllers\API\AdvertisementBannerController;
use App\Http\Controllers\API\ArtisanController;
use App\Http\Controllers\API\ArtisanReportController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\CenterController;
use App\Http\Controllers\API\CommodityController;
use App\Http\Controllers\API\CommodityIngredientController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\IngredientController;
use App\Http\Controllers\API\OrderAllocationController;
use App\Http\Controllers\API\PettyCashAllocationController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\SectorController;
use App\Http\Controllers\API\SponsorController;
use App\Http\Controllers\API\SubmitAuditStockController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
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
 // Sponsors
 Route::group(['prefix' => 'sponsors'], function () {
    Route::get('/', [SponsorController::class, 'index']);
    Route::get('/all', [SponsorController::class, 'all']);
    Route::post('/', [SponsorController::class, 'store']);
    Route::match(['patch', 'post'],'/{sponsor}', [SponsorController::class, 'update']);
    Route::delete('/{sponsor}', [SponsorController::class, 'destroy']);
});

    // Advertisement Banners
Route::group(['prefix' => 'banners'], function () {
    Route::get('/', [AdvertisementBannerController::class, 'index']);
    Route::post('/', [AdvertisementBannerController::class, 'store']);
    Route::match(['patch', 'post'], '/{advertisementBanner}', [AdvertisementBannerController::class, 'update']);
    Route::delete('/{advertisementBanner}', [AdvertisementBannerController::class, 'destroy']);
});


Route::group(['middleware' => ['auth:sanctum']], function () {


        // Order payment routes
    Route::post('orders/{id}/deposit-payment', [OrderController::class, 'recordDepositPayment']);
    Route::post('orders/{id}/balance-payment', [OrderController::class, 'recordBalancePayment']);

    // Ingredient allocation routes
    Route::post('orders/{id}/allocate-ingredients', [OrderController::class, 'allocateIngredients']);
    Route::get('orders/{id}/ingredient-allocations', [OrderController::class, 'getIngredientAllocations']);
    Route::put('orders/{orderId}/ingredient-allocations/{allocationId}', [OrderController::class, 'updateIngredientAllocation']);
    Route::delete('orders/{orderId}/ingredient-allocations/{allocationId}', [OrderController::class, 'deleteIngredientAllocation']);

    // Ingredient management routes
    Route::get('ingredients/list', [IngredientController::class, 'list']); // For dropdowns
    Route::get('ingredients/low-stock', [IngredientController::class, 'lowStock']);
    Route::post('ingredients/{id}/add-stock', [IngredientController::class, 'addStock']);
    Route::post('ingredients/{id}/reduce-stock', [IngredientController::class, 'reduceStock']);
    Route::patch('ingredients/{id}/toggle-status', [IngredientController::class, 'toggleStatus']);
    Route::get('ingredients/statistics', [IngredientController::class, 'statistics']);


    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login'])->withoutMiddleware(['auth:sanctum', 'permissions']);
        Route::delete('/logout', [AuthController::class, 'logout'])->withoutMiddleware(['permissions']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::get('/reports', [DashboardController::class, 'reports']);

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::patch('/{user}/', [UserController::class, 'update']);
        Route::patch('/{user}/change-pin', [UserController::class, 'changePin']);
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
        Route::get('/all', [SupplierController::class, 'getAll']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::patch('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoriesController::class, 'index']);
        Route::get('/getAll', [CategoriesController::class, 'getCategories']);
        Route::get('/getAllCategories', [CategoriesController::class, 'getAllCategories']);
        Route::get('/{category}', [CategoriesController::class, 'show']);
        Route::post('/', [CategoriesController::class, 'store']);
        Route::patch('/{category}', [CategoriesController::class, 'update']);
        Route::delete('/{category}', [CategoriesController::class, 'destroy']);
    });


        // In routes/api.php
    Route::get('deliveries/today', [DeliveryController::class, 'today']);
    Route::get('deliveries/statistics', [DeliveryController::class, 'statistics']);
    Route::patch('deliveries/{id}/mark-in-transit', [DeliveryController::class, 'markInTransit']);
    Route::patch('deliveries/{id}/mark-delivered', [DeliveryController::class, 'markDelivered']);
    Route::patch('deliveries/{id}/mark-failed', [DeliveryController::class, 'markFailed']);
    Route::patch('deliveries/{id}/reschedule', [DeliveryController::class, 'reschedule']);
    Route::apiResource('deliveries', DeliveryController::class);



    Route::post('petty-cash-allocation', PettyCashAllocationController::class);

    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'search']);
        Route::post('/', [ProductController::class, 'store']);
        Route::match(['patch', 'post'], '/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });

    Route::group(['prefix' => 'centers'], function () {
        Route::get('/', [CenterController::class, 'index']);
        Route::get('/all', [CenterController::class, 'getAllCentres']);
        Route::get('/getAll', [CenterController::class, 'getCentres']);
        Route::get('/{center}', [CenterController::class, 'show']);
        Route::post('/', [CenterController::class, 'store']);
        Route::patch('/{center}', [CenterController::class, 'update']);
        Route::delete('/{center}', [CenterController::class, 'destroy']);
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




    Route::prefix('artisans/{artisanId}')->group(function () {

        // Main Dashboard Data
        Route::get('/dashboard', [ArtisanReportController::class, 'getDashboardData']);

        // Orders Management
        Route::get('/orders', [ArtisanReportController::class, 'getArtisanOrders']);
        Route::post('/orders/{orderId}/accept', [ArtisanReportController::class, 'acceptOrder']);
        Route::post('/orders/{orderId}/decline', [ArtisanReportController::class, 'declineOrder']);
        Route::post('/orders/{orderId}/request-deposit', [ArtisanReportController::class, 'requestDeposit']);
        Route::post('/orders/{orderId}/start-production', [ArtisanReportController::class, 'startProduction']);
        Route::post('/orders/{orderId}/complete-production', [ArtisanReportController::class, 'completeProduction']);

        // Analytics
        Route::get('/analytics/revenue', [ArtisanReportController::class, 'getRevenueAnalytics']);
        Route::get('/analytics/costs', [ArtisanReportController::class, 'getCostAnalytics']);

        // Inventory Management
        Route::put('/inventory/{itemId}', [ArtisanReportController::class, 'updateInventory']);

    });
});


Route::group(['prefix' => 'orders', 'middleware' => ['auth:sanctum']], function () {
    // Allocation routes
    Route::get('{order}/allocation-suggestions', [OrderAllocationController::class, 'getOrderAllocationSuggestions']);
    Route::post('{order}/auto-allocate', [OrderAllocationController::class, 'autoAllocateOrder']);
    Route::post('{order}/manual-allocate', [OrderAllocationController::class, 'manualAllocateOrder']);
    Route::post('batch-allocate', [OrderAllocationController::class, 'batchAllocateOrders']);
});

Route::group(['prefix' => 'artisans', 'middleware' => ['auth:sanctum']], function () {
    Route::get('availability', [OrderAllocationController::class, 'getArtisanAvailability']);
});

Route::prefix('public')->group(function () {
        // Sector catalogue for customers

    Route::get('/sectors/catalogue', [SectorController::class, 'catalogue']);
    Route::get('/sectors/list', [SectorController::class, 'list']);
    Route::get('/sectors/{id}/public', [SectorController::class, 'showPublic']);

    // Product catalogue for customers
    Route::get('/commodities/catalogue', [CommodityController::class, 'catalogue']);
    Route::get('/commodities/featured', [CommodityController::class, 'featured']);
    Route::get('/commodities/sector/{sectorId}', [CommodityController::class, 'getBySector']);
    Route::get('/commodities/{id}/customer', [CommodityController::class, 'showForCustomer']);
});

// Protected routes - require authentication
Route::middleware(['auth:sanctum'])->group(function () {

     // Sector management
     Route::get('/sectors/list', [SectorController::class, 'list']);
     Route::get('/sectors/dashboard', [SectorController::class, 'dashboard']);
     Route::get('/sectors/available-for-products', [SectorController::class, 'availableForProducts']);
     Route::get('/sectors/{id}/capacity-check', [SectorController::class, 'checkCapacity']);
     Route::patch('/sectors/{id}/toggle-status', [SectorController::class, 'toggleStatus']);
     Route::get('/sectors/statistics', [SectorController::class, 'statistics']);
     Route::post('/sectors/bulk-action', [SectorController::class, 'bulkAction']);

     Route::apiResource('sectors', SectorController::class);



    // Commodity/Product management
    Route::apiResource('commodities', CommodityController::class);
    Route::get('/commodities/{id}/customer', [CommodityController::class, 'showForCustomer']);
    Route::patch('/commodities/{id}/toggle-status', [CommodityController::class, 'toggleStatus']);
    Route::get('/commodities/statistics', [CommodityController::class, 'statistics']);

    // Ingredient management
    Route::get('/ingredients/list', [IngredientController::class, 'list']);
    Route::get('/ingredients/low-stock', [IngredientController::class, 'lowStock']);
    Route::post('/ingredients/{id}/add-stock', [IngredientController::class, 'addStock']);
    Route::post('/ingredients/{id}/reduce-stock', [IngredientController::class, 'reduceStock']);
    Route::patch('/ingredients/{id}/toggle-status', [IngredientController::class, 'toggleStatus']);
    Route::get('/ingredients/stats/overview', [IngredientController::class, 'statistics']);

    Route::apiResource('ingredients', IngredientController::class);

    // Commodity-Ingredient relationship management (Bill of Materials)
    Route::prefix('commodities/{commodityId}')->group(function () {
        Route::get('/ingredients', [CommodityIngredientController::class, 'index']);
        Route::post('/ingredients', [CommodityIngredientController::class, 'store']);
        Route::get('/ingredients/{ingredientId}', [CommodityIngredientController::class, 'show']);
        Route::put('/ingredients/{ingredientId}', [CommodityIngredientController::class, 'update']);
        Route::delete('/ingredients/{ingredientId}', [CommodityIngredientController::class, 'destroy']);
        Route::put('/ingredients/bulk', [CommodityIngredientController::class, 'bulkUpdate']);
        Route::get('/ingredients/availability', [CommodityIngredientController::class, 'checkAvailability']);
        Route::get('/ingredients/cost-calculation', [CommodityIngredientController::class, 'calculateCost']);
    });




        // RESTful resource routes (index, store, show, update, destroy)
    Route::apiResource('orders', OrderController::class);

    // Custom workflow routes
    Route::prefix('orders')->group(function () {
        Route::get('{id}/statistics', [OrderController::class, 'statistics']);   // Order stats
        Route::patch('{id}/assign', [OrderController::class, 'assignToArtisan']); // Assign artisan

        // (Optional extra workflow methods you may add later)
        Route::patch('{id}/accept', [OrderController::class, 'acceptOrder']);           // Artisan accepts
        Route::patch('{id}/mark-deposit-paid', [OrderController::class, 'markDepositPaid']);
        Route::patch('{id}/start-production', [OrderController::class, 'startProduction']);
        Route::patch('{id}/complete-production', [OrderController::class, 'completeProduction']);
        Route::patch('{id}/mark-delivered', [OrderController::class, 'markDelivered']);
        Route::patch('{id}/cancel', [OrderController::class, 'cancelOrder']);
    });


// Custom workflow/payment routes
Route::prefix('payments')->group(function () {
    Route::get('order/{orderId}', [PaymentController::class, 'getByOrder']);   // Payments for a given order
    Route::patch('{id}/mark-completed', [PaymentController::class, 'markCompleted']); // Mark as completed
    Route::patch('{id}/refund', [PaymentController::class, 'refund']); // Refund a payment
});
});
// RESTful resource routes (index, store, show, update, destroy)
Route::apiResource('payments', PaymentController::class);





 // Additional routes that don't fit the resource pattern
 Route::prefix('artisans')->group(function () {
     Route::get('by-category', [ArtisanController::class, 'getByCategory']);
     Route::get('category/{categoryId}', [ArtisanController::class, 'getByCategoryId']);
     Route::get('center/{centerId}', [ArtisanController::class, 'getByCenterId']);
     Route::get('available/{categoryId}', [ArtisanController::class, 'getAvailableForAllocation']);
     Route::get('statistics', [ArtisanController::class, 'statistics']);
     Route::post('{id}/verify-pin', [ArtisanController::class, 'verifyPin']);
     Route::patch('{id}/toggle-status', [ArtisanController::class, 'toggleStatus']);
     Route::patch('{id}/update-pin', [ArtisanController::class, 'updatePin']);
     Route::get('{id}/profile', [ArtisanController::class, 'getProfile']);
     Route::get('{id}/orders', [ArtisanController::class, 'getOrders']);
 });
  // Standard resource routes
  Route::apiResource('artisans', ArtisanController::class);

// Admin-only routes - require admin role
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Admin commodity management
    Route::post('/commodities', [CommodityController::class, 'store']);
    Route::put('/commodities/{id}', [CommodityController::class, 'update']);
    Route::delete('/commodities/{id}', [CommodityController::class, 'destroy']);
    Route::get('/commodities/statistics', [CommodityController::class, 'statistics']);

    // Admin ingredient management
    Route::post('/ingredients', [IngredientController::class, 'store']);
    Route::put('/ingredients/{id}', [IngredientController::class, 'update']);
    Route::delete('/ingredients/{id}', [IngredientController::class, 'destroy']);
    Route::get('/ingredients/statistics', [IngredientController::class, 'statistics']);

    // Admin Bill of Materials management
    Route::prefix('commodities/{commodityId}')->group(function () {
        Route::post('/ingredients', [CommodityIngredientController::class, 'store']);
        Route::put('/ingredients/{ingredientId}', [CommodityIngredientController::class, 'update']);
        Route::delete('/ingredients/{ingredientId}', [CommodityIngredientController::class, 'destroy']);
        Route::put('/ingredients/bulk', [CommodityIngredientController::class, 'bulkUpdate']);
    });
});

// Artisan-specific routes - require artisan role
Route::middleware(['auth:sanctum', 'role:artisan'])->prefix('artisan')->group(function () {
    // Artisan can view commodities and check ingredient availability
    Route::get('/commodities', [CommodityController::class, 'index']);
    Route::get('/commodities/{id}', [CommodityController::class, 'show']);
    Route::get('/commodities/{id}/ingredients/availability', [CommodityIngredientController::class, 'checkAvailability']);
    Route::get('/commodities/{id}/ingredients/cost-calculation', [CommodityIngredientController::class, 'calculateCost']);

    // Artisan ingredient viewing (for production planning)
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::get('/ingredients/{id}', [IngredientController::class, 'show']);
    Route::get('/ingredients/low-stock', [IngredientController::class, 'lowStock']);
});
