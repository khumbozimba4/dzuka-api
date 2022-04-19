<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->float('measurement');
            $table->float('price');
            $table->float('previous_stock')->default(0);
            $table->float('stock')->default(0);
            $table->float('recently_allocated')->default(0);
            $table->string('description');
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('measurement');
            $table->dropColumn('price');
            $table->dropColumn('description');
            $table->dropColumn('previous_stock');
            $table->dropColumn('stock');
            $table->dropColumn('recently_allocated');
        });
    }
}
