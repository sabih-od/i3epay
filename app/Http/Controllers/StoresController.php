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
use App\Http\Requests\AcceptCustomerRequest;
use App\Http\Requests\CustomerUnsubscriptionRequest;
use App\Http\Requests\RejectCustomerRequest;
use App\Http\Requests\NewPackageSubscriptionRequest;
use App\Http\Requests\StoreAmountRequest;
use App\Http\Requests\StoreBalanceRequest;
use App\Http\Requests\DeductAmountRequest;
use App\Repositories\StoreRepository;
use App\Repositories\PackageRepository;
use App\Repositories\StoreBalanceRepository;
use App\Repositories\TransferHistoryRepository;
use App\Repositories\AuthenticationRepository;

use App\Helper\APIresponse;
use App\Helper\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\StoreSubscription;
use App\Models\User;

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
    protected $packageRepository;
    protected $storeBalanceRepository;
    protected $transferHistoryRepository;

    /**
     * StoresController constructor.
     *
     * @param StoreRepository $repository
     */
    public function __construct(StoreRepository $repository, PackageRepository $packageRepository, StoreBalanceRepository $storeBalanceRepository, TransferHistoryRepository $transferHistoryRepository)
    {
        $this->repository = $repository;
        $this->packageRepository = $packageRepository;
        $this->storeBalanceRepository = $storeBalanceRepository;
        $this->transferHistoryRepository = $transferHistoryRepository;
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
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

    // for customer
    /**
     * @OA\Post(
     *     path="/api/store-subscription",
     *     summary="Store subscription",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="customer_store_password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_id": 1, "customer_store_password": 1234}
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
    public function storeSubscription(CustomerSubscriptionCreateRequest $request)
    {
        try {
            // customer subscribe to the store
            $data = $this->repository->customerStoreSubscribed($request);

            // if not successfully send subscription request
            if(!$data) return APIresponse::error("Incorrect subscription request!", []);

            // customer update the store password
            $this->repository->customerUpdateStorePassword($request);

            // find store
            $store = $this->repository->find($request->input('store_id'));

            // send the store subscription notification to the vendor
            Helper::sendUserNotification($store->vendor, "You've received the new store subscription request from " . Helper::name());
            
            // return response
            return APIresponse::success('Subscription request has been send to the store!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for customer
    /**
     * @OA\Put(
     *     path="/api/store-unsubscription",
     *     summary="Store unsubscription",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="customer_store_password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_id": 1, "customer_store_password": 1234}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function storeUnsubscription(CustomerUnsubscriptionRequest $request)
    {
        try {
            // customer subscribe to the store
            $data = $this->repository->customerStoreUnsubscribed($request);

            // find store
            $store = $this->repository->find($request->input('store_id'));

            // if not successfully send unsubscription request
            if(!$data) return APIresponse::error("Incorrect unsubscription request!", []);

            // send the store subscription notification to the vendor
            Helper::sendUserNotification($store->vendor, "You've received the new store un-subscription request from " . Helper::name());
            
            // return response
            return APIresponse::success('Unsubscription request has been send to the store!', []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for customer
    /**
     * @OA\Put(
     *     path="/api/update-store-password",
     *     summary="Update store password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="customer_store_password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_id": 1, "customer_store_password": 1234}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function updateStorePassword(CustomerSubscriptionUpdateRequest $request)
    {
        try {
            // first will check that this user is customer
            // $customer = auth()->user()->hasRole('customer');

            // if(!$customer) return APIresponse::error("You don't exist in customer list!", []);

            // customer update the store password
            $this->repository->customerUpdateStorePassword($request);
            
            // return response
            return APIresponse::success('Password update successfully!', []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for customer
    /**
     * @OA\Post(
     *     path="/api/view-store-password",
     *     summary="View store password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_id": 1, "password": "test1234"}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function viewStorePassword(CustomerViewStorePasswordRequest $request)
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

    // for vendor
    // public function storeSubscriptionRequests()
    // {
    //     try {
    //         // fetch subscription request list
    //         $data = $this->repository->storeSubscriptionRequests();

    //         // return response
    //         return APIresponse::success('Subsciption requests fetched!', $data->toArray());
    //     } catch (\Throwable $th) {
    //         return APIresponse::error($th->getMessage(), []);
    //     }
    // }
    /**
     * @OA\Get(
     * path="/api/store-requests",
     * summary="Store request list",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={
                        "msg": "Store request list fetched successfully!",
                        "data": {}
                    }, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
    */
    public function storeRequests()
    {
        try {
            // fetch subscription request list
            $data = $this->repository->storeRequests();

            // return response
            return APIresponse::success('Subsciption requests fetched!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for vendor
    /**
     * @OA\Post(
     *     path="/api/accept-customer-request",
     *     summary="Accept customer request",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_subscription_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="type",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_subscription_id": 1, "type": "subscribe/unsubscribe"}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function acceptCustomerRequest(AcceptCustomerRequest $request)
    {
        try {
            // find store subscription
            $storeSubscription = StoreSubscription::query()->find($request->input('store_subscription_id'));

            if($request->input('type') == 'subscribe' || $request->input('type') == 'unsubscribe')
            {
                // check the store package limit
                if(! $this->repository->customerLimitUsage($request))
                    return APIresponse::error('Invalid request!', []);

                // accept customer request
                $data = $this->repository->acceptCustomerRequest($request);
                if(!$data) return APIresponse::error('Invalid request!', []);

                // send the accept subscription or unsubscription request notification to the customer 
                Helper::sendUserNotification($storeSubscription->customer, "Your ". $storeSubscription->store->name . " store " . $request->input('type') . " request has been accepted!");

                // return response
                return APIresponse::success('Request has been accepted!', []);
            }

            return APIresponse::error("Invalid type!", []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for vendor
    /**
     * @OA\Post(
     *     path="/api/reject-customer-request",
     *     summary="Reject customer request",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_subscription_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="type",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_subscription_id": 1, "type": "subscribe/unsubscribe"}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */ 
    public function rejectCustomerRequest(RejectCustomerRequest $request)
    {
        try {
            if($request->input('type') == 'subscribe' || $request->input('type') == 'unsubscribe')
            {
                // find store subscription
                $storeSubscription = StoreSubscription::query()->find($request->input('store_subscription_id'));
                
                $data = $this->repository->rejectCustomerRequest($request);
                if(!$data) return APIresponse::error('Invalid request!', []);

                // send the accept subscription or unsubscription request notification to the customer 
                Helper::sendUserNotification($storeSubscription->customer, "Your ". $storeSubscription->store->name . " store " . $request->input('type') . " request has been rejected!");

                // return response
                return APIresponse::success('Request has been rejected!', []);
            }

            return APIresponse::error("Invalid type!", []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for vendor
    /**
     * @OA\Post(
     *     path="/api/new-package-subscription",
     *     summary="New package subscription",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="package_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_id": 1, "package_id": 2}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function newPackageSubscription(NewPackageSubscriptionRequest $request)
    {
        DB::beginTransaction();

        try {
            // check this package is not already selected
            $verifyRequestedPackage = $this->packageRepository->verifyRequestedPackage($request);
            if($verifyRequestedPackage) return APIresponse::error('The package has already been selected!', []);

            // get the package customer limit
            $customerLimit = $this->packageRepository->find($request->package_id)->customer_limit ?? 0;

            // if new package has no customer limit
            if($customerLimit == 0) return APIresponse::error('The new package has no limit on the number of customers!', []);

            // get the total customer numbers from customer requests
            $customerLimitUsage = $this->packageRepository->customerLimitUsage($request);

            // if customer select the wrong package
            if($customerLimitUsage >= $customerLimit) return APIresponse::error('We kindly request you to select the upgraded package!', []);

            // destroy all previous packages
            $this->packageRepository->destroyAllPreviousPackages($request);

            // merged request
            $request->merge(['customer_limit' => $customerLimit, 'customer_limit_usage' => $customerLimitUsage, 'vendor_id' => auth()->user()->id]);

            // subscribe new package
            $this->packageRepository->newPackageSubscribe($request);

            DB::commit();
            return APIresponse::success("The new package has been subscribed to successfully!", []);
        } catch (\Throwable $th) {
            DB::rollback();
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for vendor
    /**
     * @OA\Get(
     * path="/api/remove-store-image/{uuid}",
     * summary="Remove storage image",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the image to be removed",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
    */
    public function removeStoreImage($uuid)
    {
        try {
            // fetch subscription request list
            $data = $this->repository->removeStoreImage($uuid);

            if(!$data) return APIresponse::error("Image does not exist in your store!", []);

            // return response
            return APIresponse::success('Removed successfully!');
            
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/store-amount",
     *     summary="Store amount",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                @OA\Property(
     *                     property="customer_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="customer_store_password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="amount",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"customer_id": 3, "customer_store_password": 1234, "store_id": 1, "amount": 50}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function storeAmount(StoreAmountRequest $request)
    {
        try {
            $payload = [
                'store_id' => $request->store_id,
                'customer_id' => $request->customer_id,
                'vendor_id' => auth()->user()->id
            ];

            // find balance amount record
            $data = $this->storeBalanceRepository->findWhere($payload)->first();

            $payload['amount'] = $request->amount;

            // find store
            $store = $this->repository->find($request->store_id);

            //find customer
            $customer = User::query()->find($request->customer_id);

            if( $data ) {
                // update amount
                $data->amount = $data->amount + $request->amount;
                $data->save();

                // create the transfer history
                $this->transferHistoryRepository->create($payload);

                // send the add amount notification to the customer
                if($store) Helper::sendUserNotification($customer, "You've added the new amount " . $request->amount . " in " . $store->name . " store!");

                // return response
                return APIresponse::success('Transfered successfully!', $data->toArray());
            }

            // create amount
            $data = $this->storeBalanceRepository->create($payload);

            // create the transfer history
            $this->transferHistoryRepository->create($payload);

            // send the add amount notification to the customer
            if($store) Helper::sendUserNotification($customer, "You've added the new amount " . $request->amount . "in " . $store->name . " store!");

            // return response
            return APIresponse::success('Transfered successfully!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/deduct-amount",
     *     summary="Deduct amount",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                @OA\Property(
     *                     property="customer_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="customer_store_password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="amount",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"customer_id": 3, "customer_store_password": 1234, "store_id": 1, "amount": 50}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function deductAmount(DeductAmountRequest $request)
    {
        try {
            $payload = [
                'store_id' => $request->store_id,
                'customer_id' => $request->customer_id,
                'vendor_id' => auth()->user()->id
            ];

            // find balance amount record
            $data = $this->storeBalanceRepository->findWhere($payload)->first();

            $payload['amount'] = $request->amount;

            // find store
            $store = $this->repository->find($request->store_id);

            //find customer
            $customer = User::query()->find($request->customer_id);

            if( $data ) {
                if($data->amount < $request->amount) return APIresponse::error("You've insufficient balance!", []);

                // update amount
                $data->amount = $data->amount - $request->amount;
                $data->save();

                 // send the add amount notification to the customer
                if($store) Helper::sendUserNotification($customer, $store->name . " store has deduct ".$request->amount. " amount from your account!");

                // return response
                return APIresponse::success('Deducted successfully!', $data->toArray());
            }

        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/store-balance",
     *     summary="Store balance",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"store_id": 1}
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
     *             @OA\Examples(example="result", value={"msg": "","data": {}}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function storeBalance(StoreBalanceRequest $request)
    {
        try {
            $payload = [];
            $payload['store_id'] = $request->store_id;
            
            if(auth()->user()->_role->name == 'vendor') {
                $payload['vendor_id'] = auth()->user()->id;

                // find balance amount record
                $data = $this->storeBalanceRepository->findWhere($payload);
                
                return APIresponse::success('Fetched successfully!', $data ? $data->toArray() : []);
            }
            if(auth()->user()->_role->name == 'customer') {
                $payload['customer_id'] = auth()->user()->id;

                // find balance amount record
                $data = $this->storeBalanceRepository->findWhere($payload)->first();
                
                // return response
                return APIresponse::success('Fetched successfully!', $data ? $data : []);
            }

            
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     * path="/api/transfer-history",
     * summary="Transfer History",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
    */
    public function transferHistory()
    {
        try {
            $data = $this->transferHistoryRepository;

            if(auth()->user()->_role->name == 'vendor')
            {
                // fetch transfer history list 
                $data = $data->with('customer')->findByField('vendor_id', auth()->user()->id);
            }

            if(auth()->user()->_role->name == 'customer')
            { 
                // fetch transfer history list
                $data = $data->with('vendor')->findByField('customer_id', auth()->user()->id);
            }

            // return response
            if($data->count() > 0) return APIresponse::success('Fetched successfully!', $data->toArray());

            return APIresponse::success("Data not found!", []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }
}