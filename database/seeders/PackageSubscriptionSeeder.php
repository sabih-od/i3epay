<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageSubscription;

class PackageSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /***** Insert packages *****/
        PackageSubscription::insert([
            'id' => 1,
            'package_id' => 1,
            'vendor_id' => 2,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
