<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreBalance extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'customer_id', 'vendor_id', 'amount'];
}
