<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\PackageRepository;
use App\Models\Package;
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
    
}
