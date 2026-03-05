<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('minimum_stock_level', 10, 3)->default(0);
            $table->decimal('maximum_stock_level', 10, 3)->nullable();
            $table->string('location_in_warehouse')->nullable(); // Aisle, shelf, bin, etc.
            $table->timestamps();

            // Unique constraint to prevent duplicate ingredient entries per warehouse
            $table->unique(['warehouse_id', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_ingredients');
    }
}