<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderAllocationService
{
    /**
     * Allocate an order to the best available artisan
     */
    public function allocateOrder(Order $order): ?Supplier
    {
        // Get order items to determine categories needed
        $orderCategories = $order->items()
            ->with('commodity.category')
            ->get()
            ->pluck('commodity.category.id')
            ->unique()
            ->filter();

        if ($orderCategories->isEmpty()) {
            throw new \Exception('Order has no valid categories');
        }

        // Find eligible artisans
        $eligibleArtisans = $this->findEligibleArtisans($orderCategories, $order);

        if ($eligibleArtisans->isEmpty()) {
            Log::warning("No eligible artisans found for order {$order->id}");
            return null;
        }

        // Score and rank artisans
        $rankedArtisans = $this->scoreAndRankArtisans($eligibleArtisans, $order);

        // Select the best artisan
        $selectedArtisan = $rankedArtisans->first();

        if ($selectedArtisan) {
            $this->assignOrderToArtisan($order, $selectedArtisan['artisan']);
            return $selectedArtisan['artisan'];
        }

        return null;
    }

    /**
     * Find artisans eligible for the order
     */
    private function findEligibleArtisans(Collection $orderCategories, Order $order): Collection
    {
        return Supplier::query()
            ->where('is_active', true)
            ->whereIn('category_id', $orderCategories->toArray())
            ->whereDoesntHave('orders', function ($query) {
                // Exclude artisans who are currently busy with other orders
                $query->whereIn('status', [
                    'assigned_to_artisan',
                    'accepted_by_artisan',
                    'deposit_paid',
                    'ingredients_provided',
                    'in_production'
                ]);
            })
            ->with(['category', 'center'])
            ->withCount([
                'orders as completed_orders_count' => function ($query) {
                    $query->where('status', 'delivered');
                },
                'orders as cancelled_orders_count' => function ($query) {
                    $query->where('status', 'cancelled');
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
            ->get();
    }

    /**
     * Score and rank artisans based on multiple factors
     */
    private function scoreAndRankArtisans(Collection $artisans, Order $order): Collection
    {
        return $artisans->map(function ($artisan) use ($order) {
            $score = $this->calculateArtisanScore($artisan, $order);

            return [
                'artisan' => $artisan,
                'score' => $score,
                'breakdown' => $score['breakdown'] ?? []
            ];
        })->sortByDesc('score.total')->values();
    }

    /**
     * Calculate comprehensive score for an artisan
     */
    private function calculateArtisanScore(Supplier $artisan, Order $order): array
    {
        $weights = config('allocation.weights', [
            'category_match' => 40,
            'availability' => 25,
            'performance' => 20,
            'location' => 10,
            'workload' => 5
        ]);

        $scores = [];

        // 1. Category Match Score (40%)
        $scores['category_match'] = $this->getCategoryMatchScore($artisan, $order);

        // 2. Availability Score (25%)
        $scores['availability'] = $this->getAvailabilityScore($artisan);

        // 3. Performance Score (20%)
        $scores['performance'] = $this->getPerformanceScore($artisan);

        // 4. Location Score (10%)
        $scores['location'] = $this->getLocationScore($artisan, $order);

        // 5. Workload Score (5%)
        $scores['workload'] = $this->getWorkloadScore($artisan);

        // Calculate weighted total
        $totalScore = 0;
        foreach ($scores as $factor => $score) {
            $totalScore += ($score * $weights[$factor]) / 100;
        }

        return [
            'total' => $totalScore,
            'breakdown' => $scores,
            'weights' => $weights
        ];
    }

    /**
     * Score based on category expertise match
     */
    private function getCategoryMatchScore(Supplier $artisan, Order $order): float
    {
        $orderCategories = $order->items()
            ->with('commodity.category')
            ->get()
            ->pluck('commodity.category.id')
            ->unique();

        // Perfect match if artisan's category is in order categories
        if ($orderCategories->contains($artisan->category_id)) {
            return 100.0;
        }

        // Partial match based on category similarity (if you have category relationships)
        // This could be enhanced with category hierarchies
        return 50.0;
    }

    /**
     * Score based on current availability
     */
    private function getAvailabilityScore(Supplier $artisan): float
    {
        // Higher score for artisans with no current orders
        if ($artisan->current_orders_count == 0) {
            return 100.0;
        }

        // Penalize based on current workload
        $currentWorkload = $artisan->current_orders_count;
        $maxWorkload = 3; // Configurable max concurrent orders

        if ($currentWorkload >= $maxWorkload) {
            return 0.0; // Should have been filtered out earlier
        }

        return max(0, 100 - ($currentWorkload * 25));
    }

    /**
     * Score based on historical performance
     */
    private function getPerformanceScore(Supplier $artisan): float
    {
        $totalOrders = $artisan->completed_orders_count + $artisan->cancelled_orders_count;

        if ($totalOrders == 0) {
            return 70.0; // Neutral score for new artisans
        }

        $successRate = $artisan->completed_orders_count / $totalOrders;

        // Convert success rate to score (0-100)
        $performanceScore = $successRate * 100;

        // Bonus for experience (more completed orders)
        $experienceBonus = min(10, $artisan->completed_orders_count * 0.5);

        return min(100, $performanceScore + $experienceBonus);
    }

    /**
     * Score based on location proximity
     */
    private function getLocationScore(Supplier $artisan, Order $order): float
    {
        // If customer provided location, calculate distance
        $customerLocation = $this->extractCustomerLocation($order);

        if (!$customerLocation || !$artisan->location) {
            return 50.0; // Neutral score if location not available
        }

        // Calculate distance (simplified - you might want to use a proper distance calculation)
        $distance = $this->calculateDistance(
            $customerLocation['lat'],
            $customerLocation['lng'],
            $artisan->location['lat'] ?? 0,
            $artisan->location['lng'] ?? 0
        );

        // Score decreases with distance (max 50km consideration)
        $maxDistance = 50; // km
        if ($distance >= $maxDistance) {
            return 0.0;
        }

        return max(0, 100 - ($distance / $maxDistance * 100));
    }

    /**
     * Score based on current workload distribution
     */
    private function getWorkloadScore(Supplier $artisan): float
    {
        // Favor artisans with lighter workloads
        $currentOrders = $artisan->current_orders_count;

        if ($currentOrders == 0) {
            return 100.0;
        }

        return max(0, 100 - ($currentOrders * 20));
    }

    /**
     * Extract customer location from order
     */
    private function extractCustomerLocation(Order $order): ?array
    {
        // This would parse the delivery_address or use customer's stored location
        // For now, return null - implement based on your location storage strategy

        if ($order->customer && isset($order->customer->location)) {
            return $order->customer->location;
        }

        return null;
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Assign order to selected artisan
     */
    private function assignOrderToArtisan(Order $order, Supplier $artisan): void
    {
        DB::transaction(function () use ($order, $artisan) {
            $order->update([
                'supplier_id' => $artisan->id,
                'status' => 'assigned_to_artisan',
                'assigned_at' => now(),
                'assigned_by' => auth()->id()
            ]);

            // Log the allocation
            Log::info("Order {$order->id} allocated to artisan {$artisan->id} ({$artisan->name})");

            // You could also send notifications here
            // $this->sendAllocationNotifications($order, $artisan);
        });
    }

    /**
     * Get allocation suggestions for manual review
     */
    public function getOrderAllocationSuggestions(Order $order, int $limit = 5): Collection
    {
        $orderCategories = $order->items()
            ->with('commodity.sector')
            ->get()
            ->pluck('commodity.sector.id')
            ->unique()
            ->filter();

        if ($orderCategories->isEmpty()) {
            return collect([]);
        }

        $eligibleArtisans = $this->findEligibleArtisans($orderCategories, $order);

        return $this->scoreAndRankArtisans($eligibleArtisans, $order)
            ->take($limit);
    }

    /**
     * Batch allocation for multiple orders
     */
    public function batchAllocateOrders(Collection $orders): array
    {
        $results = [
            'allocated' => [],
            'failed' => []
        ];

        foreach ($orders as $order) {
            try {
                $artisan = $this->allocateOrder($order);
                if ($artisan) {
                    $results['allocated'][] = [
                        'order_id' => $order->id,
                        'artisan_id' => $artisan->id,
                        'artisan_name' => $artisan->name
                    ];
                } else {
                    $results['failed'][] = [
                        'order_id' => $order->id,
                        'reason' => 'No eligible artisans found'
                    ];
                }
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'order_id' => $order->id,
                    'reason' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}