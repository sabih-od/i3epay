<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface PackageRepository.
 *
 * @package namespace App\Repositories;
 */
interface PackageRepository extends RepositoryInterface
{
    public function allPackages();
}
