<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('order_date');
            $table->string('channel');
            $table->string('sku');
            $table->string('item_description')->nullable();
            $table->string('origin');
            $table->string('so');
            $table->double('cost');
            $table->double('shipping_cost');
            $table->double('total_price');
            $table->timestamps();
            $table->unique(['so', 'sku'], 'so_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('standard_orders');
    }
}
