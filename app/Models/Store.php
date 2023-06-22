<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Store extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['name', 'description', 'address', 'category', 'store_type_id', 'vendor_id', 'package_subscription_id'];

    // protected $appends = ['is_subscribed'];

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

    public function subscriptionRequests()
    {
        return $this->hasMany(StoreSubscription::class, 'store_id', 'id')
                ->select('id', 'store_id', 'customer_id')
                ->where('is_accept', 0);
    }

    public function unsubscriptionRequests()
    {
        return $this->hasMany(StoreSubscription::class, 'store_id', 'id')
                ->select('id', 'store_id', 'customer_id')
                ->where('is_accept', '1')
                ->where('unsubscribe', '1');
    }

    public function vendorActivePackageSubscription()
    {
        return $this->hasMany(PackageSubscription::class, 'id', 'package_subscription_id')
                ->select('id', 'package_id', 'vendor_id', 'customer_limit', 'customer_limit_usage', 'expiry_date', 'status')
                ->where('vendor_id', auth()->user()->id)
                ->where('status', 1);
    }

    public function packageSubscription()
    {
        return $this->belongsTo(PackageSubscription::class, 'package_subscription_id', 'id');
    }

    public function subscribedCustomers()
    {
        return $this->hasMany(StoreSubscription::class, 'store_id', 'id')
                ->select('id', 'store_id', 'customer_id')
                ->where('is_accept', '1') // is accept customer request
                ->where('unsubscribe', '<>', '2'); // and not unsubscribed users
    }

    // public function storeBalance()
    // {
    //     $relation = $this->hasOne(StoreBalance::class)
    //                     ->select('id', 'store_id', 'customer_id', 'vendor_id', 'amount');

    //     if(auth()->user()->_role->name == 'vendor') $relation = $relation->where('vendor_id', auth()->user()->id);

    //     if(auth()->user()->_role->name == 'customer') $relation = $relation->where('customer_id', auth()->user()->id);
        
    //     return $relation;
    // }

    public function getIsSubscribedAttribute()
    {
        $data = \App\Models\StoreSubscription::query()->where('store_id', $this->id)->where('customer_id', auth()->user()->id)->where('is_accept', '1')->where('unsubscribe', '0')->first();

        if($data) return true;
        
        return false;
    }
}