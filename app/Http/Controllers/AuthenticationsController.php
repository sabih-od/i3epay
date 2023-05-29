<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterCustomerRequest;
use App\Http\Requests\RegisterVendorRequest;
use App\Http\Requests\AttemptLoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Repositories\AuthenticationRepository;
use App\Repositories\PackageSubscriptionRepository;
use App\Repositories\StoreRepository;
use App\Helper\APIresponse;

/**
 * Class AuthenticationsController.
 *
 * @package namespace App\Http\Controllers;
 */
class AuthenticationsController extends Controller
{
    /**
     * @var AuthenticationRepository
     */
    protected $repository;
    protected $packageSubscriptionRepository;
    protected $storeRepository;

    /**
     * AuthenticationsController constructor.
     *
     * @param AuthenticationRepository $repository
     * @param AuthenticationValidator $validator
     */
    public function __construct(AuthenticationRepository $repository, PackageSubscriptionRepository $packageSubscriptionRepository, StoreRepository $storeRepository)
    {
        $this->repository = $repository;
        $this->packageSubscriptionRepository = $packageSubscriptionRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login Customer / Vendor",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"email": "robertwilliam@yopmail.com", "password": "test1234"}
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
     *             @OA\Examples(example="result", value={
                    "msg": "Login successfully!",
                    "data": {
                        "access_token": "4|Iy6d8QjKfh7T5YGFP0wYBY4dgJxbKgK2pw6AOLDs",
                        "token_type": "Bearer",
                        "user": {
                        "id": 3,
                        "firstname": "Robert",
                        "lastname": "William",
                        "email": "robertwilliam@yopmail.com",
                        "phone": null,
                        "address": null,
                        "_role": {
                            "name": "customer",
                            "laravel_through_key": 3
                        }
                        }
                    }}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function attemptLogin(AttemptLoginRequest $request)
    {
        try {
            $user = $this->repository->select('id', 'firstname', 'lastname', 'email', 'password', 'phone', 'address')
            ->with(['_role' => function($query) {
                $query->select('name');
            }])
            ->whereHas('roles', function ($query) {
                $query->where('name','!=', 'admin');
            })
            ->where('email', $request->email)
            ->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json(['email' => ['The provided credentials are incorrect.']], 422);
            }
            
            // return reponse
            return APIresponse::success('Login successfully!', [
                'access_token' => $user->createToken('Personal Access Token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/register/customer",
     *     summary="Register Customer",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="firstname",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="lastname",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="phone",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="address",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"firstname": "New", "lastname": "Customer", "email": "newcustomer@yopmail.com", "phone": "1234567890", "address": "Test Address", "password": "12345678"}
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
     *             @OA\Examples(example="result", value={
                        "msg": "Customer registered successfully!",
                        "data": {
                            "access_token": "3|f0xu0g6HB0NT1YBQNDiN2wTxdvYPvTjL3WnyjsHt",
                            "token_type": "Bearer",
                            "user": {
                                "id": 7,
                                "firstname": "New",
                                "lastname": "Customer",
                                "email": "newcustomer2@yopmail.com",
                                "phone": "1234567890",
                                "address": "Test Address",
                                "_role": {
                                    "name": "customer",
                                    "laravel_through_key": 7
                                }
                            }
                        }
                    }, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function registerCustomer(RegisterCustomerRequest $request)
    {
        try {
            // Typo password converted into Hash format
            $request->merge([ 'password' => Hash::make($request->password)]);

            // Create the New User 
            $registerCustomer = $this->repository->create($request->all());

            // And assigned the customer role to the new user
            $registerCustomer->assignRole('customer');

            // find vendor from user table
            $user = $this->repository->select('id', 'firstname', 'lastname', 'email', 'password', 'phone', 'address')
            ->with(['_role' => function($query) {
                $query->select('name');
            }])
            ->whereHas('roles', function ($query) {
                $query->where('name','!=', 'admin');
            })
            ->where('email', $registerCustomer->email)
            ->first();

            // Return the success reponse
            return APIresponse::success('Customer registered successfully!', [
                'access_token' => $user->createToken('Personal Access Token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/register/vendor",
     *     summary="Register Vendor",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="firstname",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="lastname",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="address",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="category",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="package_id",
     *                     type="integer"
     *                 ),
     *                 example={"firstname": "New", "lastname": "Vendor", "email": "newvendor@yopmail.com", "address": "Test Address", "password": "12345678", "category": "Grocery Store", "package_id": 1}
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
     *             @OA\Examples(example="result", value={
                        "msg": "Vendor registered successfully!",
                        "data": {
                            "access_token": "5|xt25hgDmApEpqUlLheRtfTcRDcir9LT6FOSDt3fy",
                            "token_type": "Bearer",
                            "user": {
                                "id": 8,
                                "firstname": "New",
                                "lastname": "Vendor",
                                "email": "newvendor@yopmail.com",
                                "phone": null,
                                "address": "Test Address",
                                "_role": {
                                    "name": "vendor",
                                    "laravel_through_key": 8
                                }
                            }
                        }
                    }, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function registerVendor(RegisterVendorRequest $request)
    {
        DB::beginTransaction();

        try {
            // Typo password converted into Hash format
            $request->merge([ 'password' => Hash::make($request->password)]);

            // Create the New User 
            $registerVendor = $this->repository->create($request->all());

            // And assigned the vendor role to the new user
            $registerVendor->assignRole('vendor');

            // vendor package subscription
            $packageSubscribed = $this->packageSubscriptionRepository->create([
                'package_id' => $request->package_id,
                'vendor_id' => $registerVendor->id
            ]);

            if($packageSubscribed)
            {
                // After the successfull subscription, store will be created
                $this->storeRepository->create([
                    'name' => 'Store Name',
                    'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
                    'address' => $registerVendor->address ?? null,
                    'category' => $request->category,
                    'vendor_id' => $packageSubscribed->vendor_id,
                    'package_subscription_id' => $packageSubscribed->id
                ]);
            }

            // find vendor from user table
            $user = $this->repository->select('id', 'firstname', 'lastname', 'email', 'password', 'phone', 'address')
            ->with(['_role' => function($query) {
                $query->select('name');
            }])
            ->whereHas('roles', function ($query) {
                $query->where('name','!=', 'admin');
            })
            ->where('email', $registerVendor->email)
            ->first();

            DB::commit();

            // return response
            return APIresponse::success('Vendor registered successfully!', [
                'access_token' => $user->createToken('Personal Access Token')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return APIresponse::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Change Password (Vendor / Customer)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="current_password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="password_confirmation",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={"current_password": "test1234", "password": "test4321", "password_confirmation": "test4321"}
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
     *             @OA\Examples(example="result", value={
                        "msg": "Password changed successfully!",
                        "data": {}
                    }, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            // If current password not corrent
            if(! auth()->user() || ! Hash::check($request->current_password, auth()->user()->password)) {
                return response()->json(['current_password' => ['The current password are incorrect.']], 422);
            }

            // If current & new passwords are same
            if($request->current_password == $request->password) {
                return response()->json(['password' => ['The current and new passwords are same.']], 422);
            }

            $user = auth()->user();
            $user->password = Hash::make($request->password);
            $user = $user->save();
            
            // Return the success reponse
            return APIresponse::success('Password changed successfully!', []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }
}