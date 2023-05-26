<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\SetupRepository;
use App\Models\User;

/**
 * Class SetupRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class SetupRepositoryEloquent extends BaseRepository implements SetupRepository
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


    public function allVendors()
    {
        $data = User::role('vendor')
                    ->select('id', 'firstname', 'lastname', 'email', 'address')
                    ->get();
                    
        return $data;
    }
    
}
