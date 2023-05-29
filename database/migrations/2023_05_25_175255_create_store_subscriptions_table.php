<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('customer_store_password')->nullable();
            $table->string('is_accept')->enum('is_accept', ['1', '0'])->default(0);
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
        Schema::dropIfExists('store_subscriptions');
    }
}
