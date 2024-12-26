<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBasicInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('basic_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('invoice_number');
            $table->string('po');
            $table->string('item');
            $table->string('payment_method');
            $table->double('price');
            $table->double('tax');
            $table->double('total_price');
            $table->timestamps();
            $table->unique(['invoice_number', 'po'], 'invoice_number_po_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('basic_invoices');
    }
}
