<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->foreignId('reconciled_by')->constrained('users')->onDelete('cascade');
            $table->date('reconciliation_date');
            $table->decimal('system_stock', 10, 3); // What system says we have
            $table->decimal('physical_stock', 10, 3); // What we actually counted
            $table->decimal('variance', 10, 3); // physical - system
            $table->decimal('cost_per_unit', 10, 2);
            $table->decimal('variance_value', 10, 2); // Financial impact
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('stock_reconciliations');
    }
}
