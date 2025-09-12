<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null'); // Artisan
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', [
                'pending',
                'assigned_to_artisan',
                'accepted_by_artisan',
                'deposit_requested',
                'deposit_paid',
                'ingredients_provided',
                'in_production',
                'production_completed',
                'balance_requested',
                'balance_paid',
                'ready_for_delivery',
                'delivered',
                'cancelled'
            ])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2)->default(0);
            $table->decimal('artisan_commission', 10, 2)->default(0); // 25%
            $table->decimal('overhead_amount', 10, 2)->default(0); // 35%
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('artisan_notes')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('deposit_paid_at')->nullable();
            $table->timestamp('production_started_at')->nullable();
            $table->timestamp('production_completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
