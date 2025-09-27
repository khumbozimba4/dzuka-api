<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Services\OrderAllocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderAllocationController extends Controller
{
    protected OrderAllocationService $allocationService;

    public function __construct(OrderAllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
    }

    /**
     * Get allocation suggestions for an order
     */
    public function getOrderAllocationSuggestions(Request $request, Order $order): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);

            $suggestions = $this->allocationService->getOrderAllocationSuggestions($order, $limit);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'suggestions' => $suggestions->map(function ($suggestion) {
                        return [
                            'artisan' => [
                                'id' => $suggestion['artisan']->id,
                                'name' => $suggestion['artisan']->name,
                                'phone_number' => $suggestion['artisan']->phone_number,
                                'location' => $suggestion['artisan']->location,
                                'category' => $suggestion['artisan']->sector->name ?? null,
                                'center' => $suggestion['artisan']->center->name ?? null,
                                'completed_orders' => $suggestion['artisan']->completed_orders_count,
                                'current_orders' => $suggestion['artisan']->current_orders_count,
                            ],
                            'score' => [
                                'total' => round($suggestion['score']['total'], 2),
                                'breakdown' => $suggestion['score']['breakdown'],
                                'weights' => $suggestion['score']['weights']
                            ]
                        ];
                    }),
                    'categories_needed' => $order->items()
                        ->with('commodity.sector')
                        ->get()
                        ->pluck('commodity.sector.name')
                        ->unique()
                        ->values()
                ],
                'message' => 'Allocation suggestions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get allocation suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-allocate an order to the best available artisan
     */
    public function autoAllocateOrder(Order $order): JsonResponse
    {
        try {
            $artisan = $this->allocationService->allocateOrder($order);

            if ($artisan) {
                $order->load(['supplier', 'customer', 'items.commodity']);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'order' => $order,
                        'allocated_artisan' => [
                            'id' => $artisan->id,
                            'name' => $artisan->name,
                            'phone_number' => $artisan->phone_number,
                            'category' => $artisan->category->category_name ?? null,
                            'center' => $artisan->center->name ?? null,
                        ]
                    ],
                    'message' => "Order successfully allocated to {$artisan->name}"
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No eligible artisans found for this order'
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to allocate order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually allocate an order to a specific artisan
     */
    public function manualAllocateOrder(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'artisan_id' => 'required|exists:suppliers,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $artisan = \App\Models\Supplier::findOrFail($request->artisan_id);

            // Validate artisan is eligible
            if (!$artisan->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected artisan is not active'
                ], 422);
            }

            // Check if artisan is available (not overloaded)
            $currentOrders = $artisan->orders()
                ->whereIn('status', [
                    'assigned_to_artisan',
                    'accepted_by_artisan',
                    'deposit_paid',
                    'in_production'
                ])
                ->count();

            $maxWorkload = config('allocation.max_concurrent_orders', 3);
            if ($currentOrders >= $maxWorkload) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected artisan is currently at maximum capacity'
                ], 422);
            }

            // Perform allocation
            DB::transaction(function () use ($order, $artisan, $request) {
                $order->update([
                    'supplier_id' => $artisan->id,
                    'status' => 'assigned_to_artisan',
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                    'admin_notes' => $request->notes
                ]);
            });

            $order->load(['supplier', 'customer', 'items.commodity']);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'allocated_artisan' => [
                        'id' => $artisan->id,
                        'name' => $artisan->name,
                        'phone_number' => $artisan->phone_number,
                        'category' => $artisan->category->category_name ?? null,
                        'center' => $artisan->center->name ?? null,
                    ]
                ],
                'message' => "Order manually allocated to {$artisan->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to allocate order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch allocate multiple orders
     */
    public function batchAllocateOrders(Request $request): JsonResponse
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id'
        ]);

        try {
            $orders = Order::whereIn('id', $request->order_ids)
                ->where('status', 'pending')
                ->get();

            if ($orders->count() !== count($request->order_ids)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Some orders are not eligible for allocation'
                ], 422);
            }

            $results = $this->allocationService->batchAllocateOrders($orders);

            return response()->json([
                'status' => 'success',
                'data' => $results,
                'message' => sprintf(
                    'Batch allocation completed: %d allocated, %d failed',
                    count($results['allocated']),
                    count($results['failed'])
                )
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to batch allocate orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get artisan availability and workload info
     */
    public function getArtisanAvailability(): JsonResponse
    {
        try {
            $artisans = \App\Models\Supplier::where('is_active', true)
                ->with(['category', 'center'])
                ->withCount([
                    'orders as completed_orders_count' => function ($query) {
                        $query->where('status', 'delivered');
                    },
                    'orders as current_orders_count' => function ($query) {
                        $query->whereIn('status', [
                            'assigned_to_artisan',
                            'accepted_by_artisan',
                            'deposit_paid',
                            'in_production'
                        ]);
                    }
                ])
                ->get()
                ->map(function ($artisan) {
                    $maxWorkload = config('allocation.max_concurrent_orders', 3);
                    $availableCapacity = max(0, $maxWorkload - $artisan->current_orders_count);

                    return [
                        'id' => $artisan->id,
                        'name' => $artisan->name,
                        'phone_number' => $artisan->phone_number,
                        'category' => $artisan->category->category_name ?? null,
                        'center' => $artisan->center->name ?? null,
                        'location' => $artisan->location,
                        'current_orders' => $artisan->current_orders_count,
                        'completed_orders' => $artisan->completed_orders_count,
                        'available_capacity' => $availableCapacity,
                        'utilization_percentage' => round(($artisan->current_orders_count / $maxWorkload) * 100, 1),
                        'is_available' => $availableCapacity > 0,
                        'performance_rating' => $this->calculatePerformanceRating($artisan)
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $artisans,
                'message' => 'Artisan availability retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get artisan availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate simple performance rating
     */
    private function calculatePerformanceRating($artisan): float
    {
        $totalOrders = $artisan->completed_orders_count + ($artisan->cancelled_orders_count ?? 0);

        if ($totalOrders === 0) {
            return 3.0; // Default rating for new artisans
        }

        $successRate = $artisan->completed_orders_count / $totalOrders;
        return round(1 + ($successRate * 4), 1); // Scale to 1-5 rating
    }
}