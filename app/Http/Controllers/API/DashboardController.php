<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\Sale;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function summaries()
    {
        return response([
            'total_sales' => sizeof(Sale::findByToday()),
            'total_products_out_of_stock' => sizeof(Product::findByZeroStock())
        ]);
    }
}
