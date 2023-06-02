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
    public function verifyRequestedPackage($request);
    public function destroyAllPreviousPackages($request);
    public function customerLimitUsage($request);
    public function newPackageSubscribe($request);
}
