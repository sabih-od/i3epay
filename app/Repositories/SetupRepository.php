<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface SetupRepository.
 *
 * @package namespace App\Repositories;
 */
interface SetupRepository extends RepositoryInterface
{
    public function allVendors();
}
