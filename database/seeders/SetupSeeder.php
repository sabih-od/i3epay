<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class SetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /***** Create all roles *****/
        $adminRole = Role::create(['name' => 'admin']);
        $vendorRole = Role::create(['name' => 'vendor']);
        $customerRole = Role::create(['name' => 'customer']);
        
        /***** Create Admin User *****/
        $adminUser = User::create([
            'id' => 1,
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'admin@yopmail.com',
            'password' => Hash::make('test1234')
        ]);
        /***** Assign Admin Role To Admin User *****/
        $adminUser->assignRole($adminRole);

        /***** Create Vendor User *****/
        $vendorUser = User::create([
            'id' => 2,
            'firstname' => 'James',
            'lastname' => 'Thomas',
            'email' => 'jamesthomas@yopmail.com',
            'password' => Hash::make('test1234')
        ]);
        /***** Assign Vendor Role To Vendor User *****/
        $vendorUser->assignRole($vendorRole);

        /***** Create Customer User *****/
        $customerUser = User::create([
            'id' => 3,
            'firstname' => 'Robert',
            'lastname' => 'William',
            'email' => 'robertwilliam@yopmail.com',
            'password' => Hash::make('test1234')
        ]);
        /***** Assign Customer Role To Vendor User *****/
        $customerUser->assignRole($customerRole);
    }
}