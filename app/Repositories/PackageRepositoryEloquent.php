<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\PackageRepository;
use App\Models\Store;
use App\Models\Package;
use App\Models\PackageSubscription;
use App\Models\StoreSubscription;
use App\Validators\PackageValidator;

/**
 * Class PackageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class PackageRepositoryEloquent extends BaseRepository implements PackageRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Package::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function allPackages()
    {
        $data = Package::query()
                    ->select('id', 'name', 'description', 'price')
                    ->get();
                    
        return $data;
    }

    public function verifyRequestedPackage($request)
    {
        $data = Store::query()
                    ->whereId($request->store_id)
                    ->with(['vendorActivePackageSubscription' => function($query) use($request) {
                        $query->where('package_id', $request->package_id);
                    }])
                    ->first();

        return $data->vendorActivePackageSubscription->count();
    }

    public function destroyAllPreviousPackages($request)
    {
        $data = Store::query()
                    ->whereId($request->store_id)
                    ->with('vendorActivePackageSubscription')
                    ->first();
        if($data)
        {
            if($data->vendorActivePackageSubscription->count() > 0)
            {
                foreach ($data->vendorActivePackageSubscription as $value) {
                    $value->status = 0; // deactivate all previous packages
                    $value->save();
                }
            }
        }
    }

    public function customerLimitUsage($request)
    {
        $data = StoreSubscription::query()
                                ->whereStoreId($request->store_id)
                                ->whereIsAccept(1)
                                ->whereHas('vendorStore') // store and vendor exist in store table
                                ->whereHas('customer') // store subscription customer id exist in customer table
                                ->count();

        return $data;
    }

    public function newPackageSubscribe($request)
    {
        $data = PackageSubscription::create($request->toArray());

        if($data)
        {
            Store::query()
                ->where('id', $request->store_id)
                ->where('vendor_id', $request->vendor_id)
                ->update([
                    'package_subscription_id' => $data->id
                ]);
        }
    }
    
}
