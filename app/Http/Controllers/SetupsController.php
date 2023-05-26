<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\SetupCreateRequest;
use App\Http\Requests\SetupUpdateRequest;
use App\Repositories\SetupRepository;
use App\Repositories\PackageRepository;
use App\Helper\APIresponse;

/**
 * Class SetupsController.
 *
 * @package namespace App\Http\Controllers;
 */
class SetupsController extends Controller
{
    /**
     * @var SetupRepository
     */
    protected $repository;
    protected $packageRepository;


    /**
     * SetupsController constructor.
     *
     * @param SetupRepository $repository
     */
    public function __construct(SetupRepository $repository, PackageRepository $packageRepository)
    {
        $this->repository = $repository;
        $this->packageRepository = $packageRepository;
    }

    /**
     * @OA\Get(
     * path="/api/setup",
     * summary="Setup Api (open api) to get misc things like packages detail etc.",
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={
                        "msg": "Fetched successfully!",
                        "data": {
                            "packages": {}
                        }
                    }, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
    */
    public function index()
    {
        $data = [];

        // $data['vendors'] = $this->repository->allVendors();
        $data['packages'] = $this->packageRepository->allPackages();
        
        //Return the success reponse
        return APIresponse::success('Fetched successfully!', $data);
    }
}
