<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderAllocationService;
use Illuminate\Console\Command;

class ProcessPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:process-pending {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-allocate pending orders to available artisans';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(OrderAllocationService $allocationService)
    {
        $limit = $this->option('limit');

        $pendingOrders = Order::where('status', 'pending')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending orders found.');
            return 0;
        }

        $this->info("Processing {$pendingOrders->count()} pending orders...");

        $results = $allocationService->batchAllocateOrders($pendingOrders);

        $this->info("Allocation completed:");
        $this->info("- Allocated: " . count($results['allocated']));
        $this->info("- Failed: " . count($results['failed']));

        if (!empty($results['failed'])) {
            $this->warn("Failed orders:");
            foreach ($results['failed'] as $failure) {
                $this->line("  Order #{$failure['order_id']}: {$failure['reason']}");
            }
        }

        return 0;
    }
}
