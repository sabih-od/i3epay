<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferHistory extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'customer_id', 'vendor_id', 'amount'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id')->select('id', 'firstname', 'lastname', 'email', 'phone', 'address');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id')->select('id', 'firstname', 'lastname', 'email', 'phone', 'address');
    }
}