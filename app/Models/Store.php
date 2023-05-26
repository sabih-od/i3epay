<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'address', 'category', 'store_type_id', 'vendor_id', 'package_subscription_id'];

    public function storeType()
    {
        return $this->belongsTo(StoreType::class);
    }

    // public function vendor()
    // {
    //     return $this->hasOneThrough(
    //         User::class,
    //         StoreVendor::class,
    //         'store_id', //store vendors table
    //         'id', //users table
    //         'id', //stores table
    //         'vendor_id' //store vendors table
    //     )->role('vendor');
    // }

    public function vendor()
    {
        return $this->hasOne(User::class, 'id', 'vendor_id')->role('vendor');
    }
}