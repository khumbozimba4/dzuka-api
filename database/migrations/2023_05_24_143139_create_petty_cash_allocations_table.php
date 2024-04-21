<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePettyCashAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petty_cash_allocations', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->foreignId('center_id');
            $table->foreignId('user_id');
            $table->date('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->date('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable();
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
        Schema::dropIfExists('petty_cash_allocations');
    }
}
