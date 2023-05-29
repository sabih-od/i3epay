<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\StoreCreateRequest;
use App\Http\Requests\StoreUpdateRequest;
use App\Http\Requests\CustomerSubscriptionCreateRequest;
use App\Http\Requests\CustomerSubscriptionUpdateRequest;
use App\Http\Requests\CustomerViewStorePasswordRequest;
use App\Repositories\StoreRepository;
use App\Helper\APIresponse;
use Illuminate\Support\Facades\Hash;

/**
 * Class StoresController.
 *
 * @package namespace App\Http\Controllers;
 */
class StoresController extends Controller
{
    /**
     * @var StoreRepository
     */
    protected $repository;

    /**
     * StoresController constructor.
     *
     * @param StoreRepository $repository
     */
    public function __construct(StoreRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/all-stores",
     *     summary="All stores with filters",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="search",
     *                     type="string"
     *                 ),
     *                 example={"search": ""}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={"msg": "Fetched successfully!","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function allStores(Request $request)
    {
        try {
            //list all stores
            $data = $this->repository->allStores($request);
            
            // return response
            return APIresponse::success('Fetched successfully!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    public function customerStoreSubscription(CustomerSubscriptionCreateRequest $request)
    {
        try {
            // first will check that this user is customer
            $customer = auth()->user()->hasRole('customer');

            if(!$customer) return APIresponse::error('Customer does not exits!', []);

            // customer subscribe to the store
            $data = $this->repository->customerStoreSubscription($request);
            
            // return response
            return APIresponse::success('Subscribed successfully!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    public function customerUpdateStorePassword(CustomerSubscriptionUpdateRequest $request)
    {
        try {
            // first will check that this user is customer
            $customer = auth()->user()->hasRole('customer');

            if(!$customer) return APIresponse::error('Customer does not exits!', []);

            // customer subscribe to the store
            $this->repository->customerUpdateStorePassword($request);
            
            // return response
            return APIresponse::success('Password update successfully!', []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    public function customerViewStorePassword(CustomerViewStorePasswordRequest $request)
    {
        try {
            // if password match from request password
            if (! auth()->user() || ! Hash::check($request->input('password'), auth()->user()->password)) {
                return APIresponse::error('Incorrect Password!', []);
            }

            // fetch password
            $data = $this->repository->viewStorePassword($request);

            // return response
            return APIresponse::success('Password viewed!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }
}
