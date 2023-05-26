<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreType;

class StoreTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert store types
        StoreType::insert([
            [
                'id' => 1,
                'name' => 'Grocery Shop',
                'slug' => 'grocery-shop',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
