<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'phone',
        'address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // protected $appends = ['customer_new_store'];

    public function _role()
    {
        return $this->hasOneThrough(
            Role::class,
            ModelHasRole::class,
            'model_id',
            'id',
            'id',
            'role_id'
        )->where('model_has_roles.model_type', 'App\Models\User');
    }

    public function vendorStore()
    {
        return $this->hasOne(Store::class, 'vendor_id', 'id');
    }

    public function customerSubscribedStore()
    {
        return $this->hasManyThrough(
            Store::class, 
            StoreSubscription::class,
            'customer_id', 
            'id',
            'id',
            'store_id'
        )->where('is_accept', '1');
    }

    public function getCustomerNewStoreAttribute()
    {
        $store = collect([]);

        $subscribedStoreIds = \App\Models\StoreSubscription::query()
                                ->where('customer_id', auth()->user()->id)
                                ->where('is_accept', '1')
                                ->where('unsubscribe', '0')
                                ->get()
                                ->pluck('store_id')
                                ->toArray();

        $store = \App\Models\Store::query();

        if(count($subscribedStoreIds) > 0) {
            $store = $store->whereNotIn('id', $subscribedStoreIds);            
        }

        $store = $store->get();

        $store->map(function($collect){
            $collect->getMedia('images');
        });

        return $store;
    }
}