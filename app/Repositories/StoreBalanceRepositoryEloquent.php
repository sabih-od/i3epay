<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\StoreBalanceRepository;
use App\Models\StoreBalance;
use App\Validators\StoreBalanceValidator;

/**
 * Class StoreBalanceRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class StoreBalanceRepositoryEloquent extends BaseRepository implements StoreBalanceRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StoreBalance::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
