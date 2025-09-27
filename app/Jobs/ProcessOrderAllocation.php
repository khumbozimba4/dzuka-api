<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderAllocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderAllocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $orderId,
        private string $allocationType = 'auto'
    ) {}

    public function handle(OrderAllocationService $allocationService)
    {
        $order = Order::findOrFail($this->orderId);

        try {
            $artisan = $allocationService->allocateOrder($order);

            if ($artisan) {
                Log::info("Order {$order->id} successfully allocated to artisan {$artisan->id}");

                // Send notifications
                // NotifyArtisanOfNewOrder::dispatch($order, $artisan);
                // NotifyAdminOfAllocation::dispatch($order, $artisan);
            } else {
                Log::warning("Failed to allocate order {$order->id} - no eligible artisans");

                // NotifyAdminOfFailedAllocation::dispatch($order);
            }

        } catch (\Exception $e) {
            Log::error("Error processing allocation for order {$order->id}: " . $e->getMessage());

            // Optionally retry or escalate
            if ($this->attempts() < 3) {
                $this->release(300); // Retry after 5 minutes
            }
        }
    }
}
