<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Order;
use App\Models\Category;
use App\Models\Center;
use App\Models\Ingredient;
use App\Models\IngredientUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ArtisanReportController extends Controller
{
    /**
     * Get comprehensive dashboard data for a specific artisan
     *
     * @param int $artisanId (Supplier ID)
     * @return \Illuminate\Http\Response
     */
    public function getDashboardData($userId)
    {
        try {
            // Check if supplier exists first
            $supplier = Supplier::with(['category', 'center'])->where('user_id',$userId)->first();

            if (!$supplier) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }

            if (!$supplier->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan account is not active'
                ], 403);
            }
            $artisanId = $supplier->id;
            // Get dashboard data
            $stats = $this->getArtisanStats($artisanId);
            $monthlyRevenue = $this->getMonthlyRevenue($artisanId);
            $costBreakdown = $this->getCostBreakdown($artisanId);
            $productionMetrics = $this->getProductionMetrics($artisanId);
            $recentOrders = $this->getRecentOrders($artisanId);
            $recentFeedback = $this->getRecentFeedback($artisanId);
            $inventoryStatus = $this->getInventoryStatus($artisanId);
            $upcomingDeadlines = $this->getUpcomingDeadlines($artisanId);

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'stats' => $stats,
                    'monthly_revenue' => $monthlyRevenue,
                    'cost_breakdown' => $costBreakdown,
                    'production_metrics' => $productionMetrics,
                    'recent_orders' => $recentOrders,
                    'recent_feedback' => $recentFeedback,
                    'inventory_status' => $inventoryStatus,
                    'upcoming_deadlines' => $upcomingDeadlines
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get artisan orders with filtering
     *
     * @param int $artisanId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getArtisanOrders($artisanId, Request $request)
    {
        try {
            $supplier = Supplier::findOrFail($artisanId);

            $query = Order::with(['customer', 'items.commodity', 'payments'])
                ->where('supplier_id', $artisanId);

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept an order
     *
     * @param int $artisanId
     * @param int $orderId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function acceptOrder($artisanId, $orderId, Request $request)
    {
        try {
            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Artisan Not Found',
                ]);
            }
            $order = Order::where('supplier_id', $supplier->id)
                ->where('id', $orderId)
                ->where('status', 'assigned_to_artisan')
                ->firstOrFail();

            $order->update([
                'status' => 'accepted_by_artisan',
                'accepted_at' => now(),
                'artisan_notes' => $request->get('notes', '')
            ]);

            $order->load(['customer', 'items.commodity']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order accepted successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to accept order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decline an order
     *
     * @param int $artisanId
     * @param int $orderId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function declineOrder($artisanId, $orderId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }
            $order = Order::where('supplier_id', $supplier->id)
                ->where('id', $orderId)
                ->where('status', 'assigned_to_artisan')
                ->firstOrFail();

            $order->update([
                'status' => 'declined_by_artisan',
                'artisan_notes' => $request->reason,
                'supplier_id' => null // Remove assignment
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Order declined successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to decline order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request deposit for an order
     *
     * @param int $artisanId
     * @param int $orderId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function requestDeposit($artisanId, $orderId, Request $request)
    {
        try {
            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }
            $order = Order::where('supplier_id', $supplier->id)
                ->where('id', $orderId)
                ->where('status', 'accepted_by_artisan')
                ->firstOrFail();

            $depositAmount = $request->get('amount', $order->deposit_amount);

            $order->update([
                'status' => 'deposit_requested',
                'deposit_amount' => $depositAmount,
                'deposit_requested_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Deposit requested successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to request deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start production on an order
     *
     * @param int $artisanId
     * @param int $orderId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function startProduction($artisanId, $orderId, Request $request)
    {
        try {
            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }
            $order = Order::where('supplier_id', $supplier->id)
                ->where('id', $orderId)
                ->whereIn('status', ['deposit_paid', 'ingredients_provided'])
                ->firstOrFail();

            $estimatedCompletion = $request->get('estimated_completion');
            if ($estimatedCompletion) {
                $estimatedCompletion = Carbon::parse($estimatedCompletion);
            }

            $order->update([
                'status' => 'in_production',
                'production_started_at' => now(),
                'estimated_completion_date' => $estimatedCompletion
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Production started successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete production on an order
     *
     * @param int $artisanId
     * @param int $orderId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function completeProduction($artisanId, $orderId, Request $request)
    {
        try {
            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }
            $order = Order::where('supplier_id', $supplier->id)
                ->where('id', $orderId)
                ->where('status', 'in_production')
                ->firstOrFail();

            $order->update([
                'status' => 'production_completed',
                'production_completed_at' => now(),
                'artisan_notes' => $request->get('notes', $order->artisan_notes)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Production completed successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete production: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue analytics for artisan
     *
     * @param int $artisanId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRevenueAnalytics($artisanId, Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }
            $revenueData = $this->calculateRevenueAnalytics($artisanId, $period);
            $summary = $this->getMonthlyRevenue($artisanId);

            return response()->json([
                'status' => 'success',
                'message' => 'Revenue analytics retrieved successfully',
                'data' => [
                    'revenue_data' => $revenueData,
                    'summary' => $summary
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve revenue analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cost analytics for artisan
     *
     * @param int $artisanId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCostAnalytics($artisanId, Request $request)
    {
        try {
            $period = $request->get('period', 'month');

            $supplier = Supplier::where('user_id',$artisanId)->first();
            if(!$supplier){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artisan not found'
                ], 404);
            }
            $costBreakdown = $this->getCostBreakdown($supplier->id);
            $costTrends = $this->calculateCostTrends($artisanId, $period);

            return response()->json([
                'status' => 'success',
                'message' => 'Cost analytics retrieved successfully',
                'data' => [
                    'cost_breakdown' => $costBreakdown,
                    'cost_trends' => $costTrends
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve cost analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update inventory item
     *
     * @param int $artisanId
     * @param int $itemId
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateInventory($artisanId, $itemId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_stock' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // This would need an inventory table - for now, we'll use ingredient usage
            $inventoryItem = IngredientUsage::where('supplier_id', $artisanId)
                ->where('id', $itemId)
                ->firstOrFail();

            $inventoryItem->update([
                'remaining_quantity' => $request->current_stock,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Inventory updated successfully',
                'data' => $inventoryItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods

    private function getArtisanStats($artisanId)
    {
        $totalOrders = Order::where('supplier_id', $artisanId)->count();
        $completedOrders = Order::where('supplier_id', $artisanId)
            ->where('status', 'delivered')->count();
        $activeOrders = Order::where('supplier_id', $artisanId)
            ->whereIn('status', ['accepted_by_artisan', 'deposit_paid', 'in_production'])
            ->count();
        $pendingOrders = Order::where('supplier_id', $artisanId)
            ->where('status', 'assigned_to_artisan')->count();

        $totalRevenue = Order::where('supplier_id', $artisanId)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $totalCommission = Order::where('supplier_id', $artisanId)
            ->where('status', 'delivered')
            ->sum('artisan_commission');

        $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'active_orders' => $activeOrders,
            'pending_orders' => $pendingOrders,
            'declined_orders' => Order::where('supplier_id', $artisanId)
                ->where('status', 'declined_by_artisan')->count(),
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
            'net_profit' => $totalCommission * 0.75, // Assuming 25% overhead
            'average_rating' => 4.5, // This would come from a ratings table
            'completion_rate' => round($completionRate, 2),
            'on_time_delivery_rate' => 85.0, // This would be calculated from delivery data
            'customer_satisfaction' => 4.3 // This would come from feedback
        ];
    }

    private function getMonthlyRevenue($artisanId)
    {
        $currentMonth = Order::where('supplier_id', $artisanId)
            ->where('status', 'delivered')
            ->whereMonth('delivered_at', now()->month)
            ->whereYear('delivered_at', now()->year)
            ->sum('artisan_commission');

        $previousMonth = Order::where('supplier_id', $artisanId)
            ->where('status', 'delivered')
            ->whereMonth('delivered_at', now()->subMonth()->month)
            ->whereYear('delivered_at', now()->subMonth()->year)
            ->sum('artisan_commission');

        $growthPercentage = $previousMonth > 0
            ? (($currentMonth - $previousMonth) / $previousMonth) * 100
            : 0;

        $yearlyTotal = Order::where('supplier_id', $artisanId)
            ->where('status', 'delivered')
            ->whereYear('delivered_at', now()->year)
            ->sum('artisan_commission');

        return [
            'current_month' => $currentMonth,
            'previous_month' => $previousMonth,
            'growth_percentage' => round($growthPercentage, 2),
            'yearly_total' => $yearlyTotal
        ];
    }

    private function getCostBreakdown($artisanId)
    {
        // Calculate ingredient costs = sum(quantity_used * cost_per_unit) + variance adjustments
        $ingredientCosts = IngredientUsage::where('supplier_id', $artisanId)
            ->whereMonth('created_at', now()->month)
            ->get()
            ->sum(function ($usage) {
                return ($usage->quantity_used * $usage->cost_per_unit) + $usage->variance_cost;
            });

        $labor = $ingredientCosts * 0.6; // Estimated labor costs
        $utilities = 5000; // Fixed utility costs
        $equipment = 2000; // Equipment depreciation/maintenance
        $otherExpenses = 1500;

        return [
            'ingredients' => $ingredientCosts,
            'labor' => $labor,
            'utilities' => $utilities,
            'equipment' => $equipment,
            'other_expenses' => $otherExpenses,
            'total_costs' => $ingredientCosts + $labor + $utilities + $equipment + $otherExpenses
        ];
    }


    private function getProductionMetrics($artisanId)
    {
        $ordersInProduction = Order::where('supplier_id', $artisanId)
            ->where('status', 'in_production')
            ->count();

        return [
            'average_production_time' => 72, // hours - would be calculated from actual data
            'orders_in_production' => $ordersInProduction,
            'production_efficiency' => 82.5, // percentage
            'resource_utilization' => 75.0, // percentage
            'quality_score' => 4.2
        ];
    }

    private function getRecentOrders($artisanId, $limit = 10)
    {
        return Order::with(['customer', 'items.commodity'])
            ->where('supplier_id', $artisanId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getRecentFeedback($artisanId, $limit = 5)
    {
        // This would come from a feedback/ratings table
        return []; // Placeholder
    }

    // private function getInventoryStatus($artisanId)
    // {
    //     // This would come from an inventory table
    //     return IngredientUsage::where('supplier_id', $artisanId)
    //         ->where('remaining_quantity', '>', 0)
    //         ->get()
    //         ->map(function($item) {
    //             return [
    //                 'id' => $item->id,
    //                 'name' => $item->ingredient_name ?? 'Unknown Item',
    //                 'current_stock' => $item->remaining_quantity,
    //                 'unit' => 'kg',
    //                 'minimum_required' => 10,
    //                 'cost_per_unit' => $item->unit_cost ?? 0,
    //                 'last_restocked' => $item->updated_at
    //             ];
    //         });
    // }

    private function getInventoryStatus($artisanId)
    {
    return Ingredient::whereHas('usage', function($query) use ($artisanId) {
            $query->where('supplier_id', $artisanId);
        })
        ->active()
        ->get()
        ->map(function($ingredient) {
            return [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'current_stock' => $ingredient->current_stock,
                'unit' => $ingredient->unit_of_measurement,
                'minimum_required' => $ingredient->minimum_stock_level,
                'cost_per_unit' => $ingredient->cost_per_unit,
                'last_restocked' => $ingredient->updated_at
            ];
        });
}


    private function getUpcomingDeadlines($artisanId)
    {
        return Order::with('customer')
            ->where('supplier_id', $artisanId)
            ->whereNotNull('estimated_completion_date')
            ->where('estimated_completion_date', '>=', now())
            ->orderBy('estimated_completion_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($order) {
                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'Unknown',
                    'deadline' => $order->estimated_completion_date,
                    'status' => $order->status
                ];
            });
    }

    private function calculateRevenueAnalytics($artisanId, $period)
    {
        // Implementation for different periods (week, month, quarter, year)
        $periods = [];
        $startDate = now()->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $periodStart = $startDate->copy()->addMonths($i);
            $periodEnd = $periodStart->copy()->endOfMonth();

            $revenue = Order::where('supplier_id', $artisanId)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [$periodStart, $periodEnd])
                ->sum('total_amount');

            $commission = Order::where('supplier_id', $artisanId)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [$periodStart, $periodEnd])
                ->sum('artisan_commission');

            $orderCount = Order::where('supplier_id', $artisanId)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [$periodStart, $periodEnd])
                ->count();

            $periods[] = [
                'period' => $periodStart->format('M Y'),
                'revenue' => $revenue,
                'commission' => $commission,
                'orders' => $orderCount
            ];
        }

        return $periods;
    }

    private function calculateCostTrends($artisanId, $period)
    {
        // Similar to revenue analytics but for costs
        return []; // Placeholder implementation
    }
}