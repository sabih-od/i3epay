<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\StoreRepository;
use App\Models\Store;
use App\Models\StoreSubscription;
use App\Validators\StoreValidator;
use Illuminate\Support\Facades\DB;

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
        $data = Store::query()
        ->select('id', 'name', 'description', 'address', 'store_type_id', 'vendor_id')
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

        $data = $data->map(function($collect) {
            return [
                'id' => $collect['id'],
                'name' => $collect['name'],
                'description' => $collect['description'],
                'address' => $collect['address'],
                'store_type_id' => $collect['store_type_id'],
                'vendor_id' => $collect['vendor_id'],
                'store_type' => $collect['store_type'],
                'vendor' => $collect['vendor'],
                'images' => $collect->getMedia('images')->map(function($image){
                    return $image->original_url;
                })
            ];
        });

        return $data;
    }

    public function customerStoreSubscribed($request)
    {
        // customer send subscription request to the store
        $data = StoreSubscription::create([
            'store_id' => $request->input('store_id'),
            'customer_id' => auth()->user()->id
        ]);

        return $data;
    }

    public function customerStoreUnsubscribed()
    {
        // customer send to the unsubscription request to the store
        $data = StoreSubscription::where('is_accept', '1')->where('unsubscribe', '0')->update([
            'unsubscribe' => '1'
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

    // public function storeSubscriptionRequests()
    // {
    //     $data = Store::select('id', 'name', 'description', 'address', 'store_type_id', 'vendor_id')
    //             ->with(['storeType:id,name,slug', 'vendor' => function($query){
    //                 $query->select('id', 'firstname', 'lastname', 'email', 'phone', 'address');
    //             }, 'subscriptionRequests', 'subscriptionRequests.customer'])
    //             // if user exists in users table with vendor role 
    //             ->whereHas('vendor')
    //             ->where('vendor_id', auth()->user()->id);

    //     $data = $data->get();
    //     return $data;
    // }

    public function storeRequests()
    {
        $data = Store::select('id', 'name', 'description', 'address', 'store_type_id', 'vendor_id')
                ->with(['storeType:id,name,slug', 'vendor' => function($query){
                    $query->select('id', 'firstname', 'lastname', 'email', 'phone', 'address');
                }, 'subscriptionRequests', 'subscriptionRequests.customer', 'unsubscriptionRequests', 'unsubscriptionRequests.customer'])
                // if user exists in users table with vendor role 
                ->whereHas('vendor')
                ->where('vendor_id', auth()->user()->id);

        $data = $data->get();
        return $data;
    }

    public function acceptCustomerRequest($request)
    {
        DB::beginTransaction();

        $data = StoreSubscription::query()
                ->where('id', $request->input('store_subscription_id')) // if store subscription id exist
                ->whereHas('vendorStore') // store and vendor exist in store table
                ->whereHas('customer'); // store subscription customer id exist in customer table

        $data_ = clone $data;

        if($request->input('type') == 'subscribe') {
            $data = $data->where('is_accept', '0') // request does not have accept before
                    ->where('unsubscribe', '0') // if not send any unsubscription request or not have unsubscribed
                    ->update([
                        'is_accept' => 1
                    ]);
            
            if(!$data) DB::rollBack();

            DB::commit();
            return $data;
        }

        if($request->input('type') == 'unsubscribe') {
            $data = $data->where('is_accept', '1') // check vendor has already accept customer request
                    ->where('unsubscribe', '1') // if not send any unsubscription request or not have unsubscribed
                    ->update([
                        'unsubscribe' => '2'
                    ]);
            if(!$data) DB::rollBack();
            
            $data_ = $data_->where('unsubscribe', '2')->delete();
            if(!$data_) DB::rollBack();

            DB::commit();
            return $data_;
        }
    }

    public function rejectCustomerRequest($request)
    {
        DB::beginTransaction();

        $data = StoreSubscription::query()
                ->where('id', $request->input('store_subscription_id')) // if store subscription id exist
                ->whereHas('vendorStore') // store and vendor exist in store table
                ->whereHas('customer'); // store subscription customer id exist in customer table

        if($request->input('type') == 'subscribe') {
            $data = $data->where('is_accept', '0') // request does not have accept before
                    ->where('unsubscribe', '0') // if not send any unsubscription request or not have unsubscribed
                    ->delete();
            if(!$data) DB::rollBack();
        }

        if($request->input('type') == 'unsubscribe') {
            $data = $data->where('is_accept', '1') // request does not have accept before
                    ->where('unsubscribe', '1') // if not send any unsubscription request or not have unsubscribed
                    ->update([
                        'unsubscribe' => '0'
                    ]);
            if(!$data) DB::rollBack();
        }

        DB::commit();
        return $data;
    }
}