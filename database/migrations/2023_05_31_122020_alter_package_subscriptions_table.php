<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPackageSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_subscriptions', function (Blueprint $table) {
            $table->integer('customer_limit')->default(0)->after('vendor_id');
            $table->integer('customer_limit_usage')->default(0)->after('customer_limit');
            $table->date('expiry_date')->nullable()->after('customer_limit_usage');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_subscriptions', function (Blueprint $table) {
            $table->dropColumn('customer_limit');
            $table->dropColumn('customer_limit_usage');
            $table->dropColumn('expiry_date');
            $table->dropColumn('deleted_at');
        });
    }
}
