<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAcceptColumnInStoreSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_subscriptions', function (Blueprint $table) {
            $table->string('is_accept')->enum('is_accept', ['1', '0'])->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_accept');
        });
    }
}
