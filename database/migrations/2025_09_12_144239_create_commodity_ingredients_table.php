<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommodityIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commodity_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commodity_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_required', 10, 3); // Quantity needed for one unit
            $table->decimal('cost_per_unit', 10, 2); // Cost at time of BOQ creation
            $table->timestamps();

            $table->unique(['commodity_id', 'ingredient_id']);
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commodity_ingredients');
    }
}
