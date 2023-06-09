<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class PackageSubscription.
 *
 * @package namespace App\Models;
 */
class PackageSubscription extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['package_id', 'vendor_id', 'status', 'customer_limit', 'customer_limit_usage', 'expiry_date'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

}
