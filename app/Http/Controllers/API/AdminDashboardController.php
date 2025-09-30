<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Payment;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
/**
     * Get comprehensive admin dashboard statistics
     * GET /api/admin/dashboard/statistics
     */
    public function statistics()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            // Order Statistics
            'totalOrders' => Order::count(),
            'newOrders' => Order::whereDate('created_at', $today)
                ->where('status', Order::STATUS_PENDING)
                ->count(),
            'pendingOrders' => Order::where('status', Order::STATUS_PENDING)->count(),
            'completedOrders' => Order::where('status', Order::STATUS_DELIVERED)->count(),
            'cancelledOrders' => Order::where('status', Order::STATUS_CANCELLED)->count(),

            // Revenue Statistics
            'totalRevenue' => (float) Order::where('status', Order::STATUS_DELIVERED)
                ->sum('total_amount'),
            'monthlyRevenue' => (float) Order::where('status', Order::STATUS_DELIVERED)
                ->where('created_at', '>=', $thisMonth)
                ->sum('total_amount'),
            'overheadShare' => (float) Order::where('status', Order::STATUS_DELIVERED)
                ->sum('overhead_amount'),
            'artisanCommissions' => (float) Order::where('status', Order::STATUS_DELIVERED)
                ->sum('artisan_commission'),

            // Artisan Statistics
            'totalArtisans' => Supplier::count(),
            'activeArtisans' => Supplier::where('is_active', true)->count(),
            'inactiveArtisans' => Supplier::where('is_active', false)->count(),

            // Customer Statistics
            'totalCustomers' => User::where('role_id', '4')->count(),
            'activeCustomers' => User::where('role_id', '4')
                ->whereHas('orders', function($q) use ($thisMonth) {
                    $q->where('created_at', '>=', $thisMonth);
                })
                ->count(),

            // Payment & Deposit Statistics
            'pendingDeposits' => Order::where('status', Order::STATUS_DEPOSIT_REQUESTED)->count(),
            'pendingBalance' => Order::where('status', Order::STATUS_BALANCE_REQUESTED)->count(),
            'totalDepositsCollected' => (float) Payment::where('payment_type', Payment::TYPE_DEPOSIT)
                ->where('status', Payment::STATUS_COMPLETED)
                ->sum('amount'),
            'totalBalancesCollected' => (float) Payment::where('payment_type', Payment::TYPE_BALANCE)
                ->where('status', Payment::STATUS_COMPLETED)
                ->sum('amount'),

            // Inventory Statistics
            'lowStockItems' => Ingredient::whereColumn('current_stock', '<=', 'minimum_stock_level')->count(),
            'outOfStockItems' => Ingredient::where('current_stock', 0)->count(),
            'totalIngredients' => Ingredient::count(),

            // Order Status Breakdown
            'ordersByStatus' => Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->status => $item->count];
                }),
        ];

        // Calculate platform profit (40% after artisan and overhead)
        $stats['platformProfit'] = $stats['totalRevenue'] * 0.40;

        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }

    /**
     * Get revenue breakdown and distribution
     * GET /api/admin/dashboard/revenue-breakdown
     */
    public function revenueBreakdown()
    {
        $totalRevenue = (float) Order::where('status', Order::STATUS_DELIVERED)
            ->sum('total_amount');

        $breakdown = [
            'total_revenue' => $totalRevenue,
            'artisan_share' => [
                'amount' => $totalRevenue * 0.25,
                'percentage' => 25
            ],
            'overhead_share' => [
                'amount' => $totalRevenue * 0.35,
                'percentage' => 35
            ],
            'platform_profit' => [
                'amount' => $totalRevenue * 0.40,
                'percentage' => 40
            ],
            'breakdown_details' => [
                'total_orders_delivered' => Order::where('status', Order::STATUS_DELIVERED)->count(),
                'average_order_value' => Order::where('status', Order::STATUS_DELIVERED)->avg('total_amount'),
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $breakdown,
            'message' => 'Revenue breakdown retrieved successfully'
        ]);
    }

    /**
     * Get sector performance statistics
     * GET /api/admin/dashboard/sector-performance
     */
    public function sectorPerformance()
    {
        $sectors = Sector::withCount('commodities')
            ->get()
            ->map(function ($sector) {
                // Get orders for commodities in this sector
                $orderStats = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('commodities', 'order_items.commodity_id', '=', 'commodities.id')
                    ->where('commodities.sector_id', $sector->id)
                    ->where('orders.status', Order::STATUS_DELIVERED)
                    ->selectRaw('COUNT(DISTINCT orders.id) as total_orders')
                    ->selectRaw('SUM(order_items.total_price) as total_revenue')
                    ->first();

                return [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'description' => $sector->description,
                    'image' => $sector->image,
                    'commodity_count' => $sector->commodities_count,
                    'total_orders' => $orderStats->total_orders ?? 0,
                    'total_revenue' => (float) ($orderStats->total_revenue ?? 0),
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
            'message' => 'Sector performance retrieved successfully'
        ]);
    }

    /**
     * Get recent orders for dashboard
     * GET /api/admin/dashboard/recent-orders
     */
    public function recentOrders(Request $request)
    {
        $limit = $request->get('limit', 10);

        $orders = Order::with([
            'customer:id,name,email',
            'supplier:id,name',
            'items.commodity:id,name'
        ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'Recent orders retrieved successfully'
        ]);
    }

    /**
     * Get critical alerts for dashboard
     * GET /api/admin/dashboard/alerts
     */
    public function alerts()
    {
        $alerts = [];

        // New orders pending allocation
        $newOrders = Order::where('status', Order::STATUS_PENDING)
            ->whereDate('created_at', '>=', now()->subDays(2))
            ->count();

        if ($newOrders > 0) {
            $alerts[] = [
                'type' => 'urgent',
                'category' => 'orders',
                'title' => 'New Orders Pending',
                'message' => "{$newOrders} orders need artisan allocation",
                'count' => $newOrders,
                'action_url' => '/admin/orders/new'
            ];
        }

        // Low stock items
        $lowStock = Ingredient::whereColumn('current_stock', '<=', 'minimum_stock_level')
            ->where('current_stock', '>', 0)
            ->count();

        if ($lowStock > 0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'inventory',
                'title' => 'Low Inventory',
                'message' => "{$lowStock} ingredients running low",
                'count' => $lowStock,
                'action_url' => '/admin/inventory'
            ];
        }

        // Out of stock items
        $outOfStock = Ingredient::where('current_stock', 0)->count();

        if ($outOfStock > 0) {
            $alerts[] = [
                'type' => 'error',
                'category' => 'inventory',
                'title' => 'Out of Stock',
                'message' => "{$outOfStock} ingredients are out of stock",
                'count' => $outOfStock,
                'action_url' => '/admin/inventory'
            ];
        }

        // Pending deposits
        $pendingDeposits = Order::where('status', Order::STATUS_DEPOSIT_REQUESTED)->count();

        if ($pendingDeposits > 0) {
            $alerts[] = [
                'type' => 'info',
                'category' => 'payments',
                'title' => 'Pending Deposits',
                'message' => "{$pendingDeposits} customers need to pay deposits",
                'count' => $pendingDeposits,
                'action_url' => '/admin/orders?status=deposit_requested'
            ];
        }

        // Pending balance payments
        $pendingBalance = Order::where('status', Order::STATUS_BALANCE_REQUESTED)->count();

        if ($pendingBalance > 0) {
            $alerts[] = [
                'type' => 'info',
                'category' => 'payments',
                'title' => 'Pending Balance Payments',
                'message' => "{$pendingBalance} orders awaiting balance payment",
                'count' => $pendingBalance,
                'action_url' => '/admin/orders?status=balance_requested'
            ];
        }

        // Overdue orders (in production for more than 14 days)
        $overdueOrders = Order::where('status', Order::STATUS_IN_PRODUCTION)
            ->where('production_started_at', '<=', now()->subDays(14))
            ->count();

        if ($overdueOrders > 0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'orders',
                'title' => 'Overdue Orders',
                'message' => "{$overdueOrders} orders in production over 14 days",
                'count' => $overdueOrders,
                'action_url' => '/admin/orders?status=in_production'
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $alerts,
            'count' => count($alerts),
            'message' => 'Dashboard alerts retrieved successfully'
        ]);
    }

    /**
     * Get top performing artisans
     * GET /api/admin/dashboard/top-artisans
     */
    public function topArtisans(Request $request)
    {
        $limit = $request->get('limit', 10);

        $artisans = Supplier::with(['category', 'center'])
            ->withCount([
                'orders',
                'completedOrders'
            ])
            ->select('suppliers.*')
            ->selectRaw('(SELECT SUM(artisan_commission) FROM orders WHERE orders.supplier_id = suppliers.id AND orders.status = ?) as total_commission', [Order::STATUS_DELIVERED])
            ->orderBy('total_commission', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $artisans,
            'message' => 'Top artisans retrieved successfully'
        ]);
    }

    /**
     * Get monthly trends data
     * GET /api/admin/dashboard/trends
     */
    public function trends(Request $request)
    {
        $months = $request->get('months', 6);

        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $monthData = [
                'month' => $startDate->format('M Y'),
                'orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
                'revenue' => (float) Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', Order::STATUS_DELIVERED)
                    ->sum('total_amount'),
                'completed_orders' => Order::whereBetween('delivered_at', [$startDate, $endDate])
                    ->where('status', Order::STATUS_DELIVERED)
                    ->count(),
                'new_customers' => User::where('role_id', '4')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            $trends[] = $monthData;
        }

        return response()->json([
            'status' => 'success',
            'data' => $trends,
            'message' => 'Trends data retrieved successfully'
        ]);
    }

}