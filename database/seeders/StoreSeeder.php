<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /***** Insert stores *****/
        Store::insert([
            [
                'id' => 1,
                'name' => 'Bushwick Avenue',
                'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                'address' => 'Grocery Store, Bushwick Avenue, Brooklyn, NY, USA',
                'category' => 'Grocery Store',
                'vendor_id' => 2,
                'package_subscription_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}