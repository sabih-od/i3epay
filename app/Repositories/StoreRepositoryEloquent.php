<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\StoreRepository;
use App\Models\Store;
use App\Models\StoreSubscription;
use App\Validators\StoreValidator;

/**
 * Class StoreRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class StoreRepositoryEloquent extends BaseRepository implements StoreRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Store::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    


    public function allStores($request) 
    {
        $data = Store::select('id', 'name', 'description', 'address', 'store_type_id', 'vendor_id')
        ->with(['storeType:id,name,slug', 'vendor' => function($query){
            $query->select('id', 'firstname', 'lastname', 'email', 'phone', 'address');
        }])
        // if user exists in users table with vendor role 
        ->whereHas('vendor');

        if($request->has('search')) {
            $data = $data->where(function($query) use($request){
                $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('description', 'like', '%' . $request->input('search') . '%')
                ->orWhere('address', 'like', '%' . $request->input('search') . '%')
                ->orWhereHas('storeType', function($query) use($request){
                    $query->where('name', 'like', '%' . $request->input('search') . '%');
                });
            });
        }

        $data = $data->get();

        return $data;
    }

    public function customerStoreSubscription($request)
    {
        // customer send subscription request to the store
        $data = StoreSubscription::create([
            'store_id' => $request->input('store_id'),
            'customer_id' => auth()->user()->id
        ]);

        return $data;
    }

    public function customerUpdateStorePassword($request)
    {
        // customer update store password
        $data = StoreSubscription::where('store_id', $request->input('store_id'))
                ->where('customer_id', auth()->user()->id)
                ->update([
                    'customer_store_password' => $request->input('customer_store_password')
                ]);

        return $data;
    }

    public function viewStorePassword($request)
    {
        // customer view store password
        $data = StoreSubscription::query()
                ->select('customer_store_password')
                ->where('store_id', $request->input('store_id'))
                ->where('customer_id', auth()->user()->id)
                ->first();

        return $data;
    }
}
