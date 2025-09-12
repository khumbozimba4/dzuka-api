<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredient_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade'); // Artisan
            $table->decimal('quantity_used', 10, 3);
            $table->decimal('quantity_allocated', 10, 3);
            $table->decimal('variance', 10, 3); // usage - allocated (positive = over usage)
            $table->decimal('cost_per_unit', 10, 2);
            $table->decimal('variance_cost', 10, 2); // Financial impact of variance
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
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
        Schema::dropIfExists('ingredient_usage');
    }
}
