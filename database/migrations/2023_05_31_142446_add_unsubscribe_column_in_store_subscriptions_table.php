<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnsubscribeColumnInStoreSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_subscriptions', function (Blueprint $table) {
            $table->enum('unsubscribe', ['2', '1', '0'])->default('0')->after('is_accept');
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
            $table->dropColumn('unsubscribe');
        });
    }
}
