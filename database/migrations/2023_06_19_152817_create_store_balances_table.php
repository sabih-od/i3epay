<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_balances', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('vendor_id')->nullable();
            $table->decimal('amount')->nullable();
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
        Schema::dropIfExists('store_balances');
    }
}
