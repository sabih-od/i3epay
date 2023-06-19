<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\TransferHistoryRepository;
use App\Models\TransferHistory;
use App\Validators\TransferHistoryValidator;

/**
 * Class TransferHistoryRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class TransferHistoryRepositoryEloquent extends BaseRepository implements TransferHistoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return TransferHistory::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
