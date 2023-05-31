<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['store_id', 'customer_id', 'customer_store_password', 'is_accept', 'unsubscribe'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id')->select('id','firstname','lastname','email','phone','address')->role('customer');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function vendorStore()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id')->where('vendor_id', auth()->user()->id);
    }
}