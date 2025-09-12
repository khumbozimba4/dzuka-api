<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtisanReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artisan_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade'); // Artisan
            $table->date('report_date');
            $table->integer('orders_completed');
            $table->decimal('total_revenue', 10, 2); // Total sales value
            $table->decimal('total_commission', 10, 2); // 25% commission earned
            $table->decimal('material_costs', 10, 2); // Cost of ingredients used
            $table->decimal('overhead_costs', 10, 2); // 35% overhead allocation
            $table->decimal('net_profit', 10, 2); // Commission - any deductions
            $table->decimal('ingredient_variance_cost', 10, 2)->default(0); // Cost of over-usage
            $table->timestamps();

            $table->unique(['supplier_id', 'report_date']);        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artisan_reports');
    }
}
