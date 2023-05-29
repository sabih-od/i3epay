<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSubscription extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'customer_id', 'customer_store_password', 'is_accept'];

    public $timestamps = false;
}
