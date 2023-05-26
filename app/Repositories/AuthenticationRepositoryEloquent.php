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
    
}
