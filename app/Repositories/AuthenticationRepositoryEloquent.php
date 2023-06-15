<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\AuthenticationRepository;
use App\Models\User;
use App\Validators\AuthenticationValidator;

/**
 * Class AuthenticationRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class AuthenticationRepositoryEloquent extends BaseRepository implements AuthenticationRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
    public function showProfile()
    {
        $data = User::whereId(auth()->user()->id)->select('id', 'firstname', 'lastname', 'email', 'phone', 'address');

        if(auth()->user()->_role->name == 'vendor')
        {
            $data = $data->with(['vendorStore' => function($query) {
                        $query->select('id', 'name', 'description', 'address', 'store_type_id', 'vendor_id', 'package_subscription_id');
                    }, 
                        'vendorStore.vendorActivePackageSubscription', 
                        'vendorStore.vendorActivePackageSubscription.package' => function($query) {
                            $query->select('id', 'name', 'description', 'price', 'customer_limit');
                        }
                    ]);
            
        }

        if(auth()->user()->_role->name == 'customer')
        {
            $data = $data->with(['customerSubscribedStore']);
        }

        $data = $data->first();

        if(auth()->user()->_role->name == 'vendor')
        {
            $data->vendorStore->getMedia('images');

            //     $data->vendorStore->images = $data->vendorStore->getMedia('images')->map(function($image){
            //         return $image->original_url;
            //     });
        }
        if(auth()->user()->_role->name == 'customer')
        {
            $data->customerSubscribedStore->map(function($collect){
                $collect->getMedia('images');
            });
        }

        return $data;
    }
}