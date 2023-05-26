<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\PackageSubscriptionRepository;
use App\Models\PackageSubscription;
use App\Validators\PackageSubscriptionValidator;

/**
 * Class PackageSubscriptionRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class PackageSubscriptionRepositoryEloquent extends BaseRepository implements PackageSubscriptionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PackageSubscription::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
