<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;
use App\Models\Store;

/**
 * Interface StoreRepository.
 *
 * @package namespace App\Repositories;
 */
interface StoreRepository extends RepositoryInterface
{
    public function allStores($request);
    public function customerStoreSubscribed($request);
    // public function storeSubscriptionRequests();
    public function storeRequests();
    public function acceptCustomerRequest($request);
}
