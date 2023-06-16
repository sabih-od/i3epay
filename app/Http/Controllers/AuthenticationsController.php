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
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Repositories\AuthenticationRepository;
use App\Repositories\PackageSubscriptionRepository;
use App\Repositories\StoreRepository;
use App\Repositories\PackageRepository;
use App\Helper\APIresponse;
use Illuminate\Support\Facades\Password;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Traits\PHPCustomMail;

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
    protected $packageRepository;

    /**
     * AuthenticationsController constructor.
     *
     * @param AuthenticationRepository $repository
     * @param AuthenticationValidator $validator
     */
    public function __construct(AuthenticationRepository $repository, PackageSubscriptionRepository $packageSubscriptionRepository, StoreRepository $storeRepository, PackageRepository $packageRepository)
    {
        $this->repository = $repository;
        $this->packageSubscriptionRepository = $packageSubscriptionRepository;
        $this->storeRepository = $storeRepository;
        $this->packageRepository = $packageRepository;
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
                    "data": {}
                    }, summary="An result object."),
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
                        "data": {}
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
     *             mediaType="multipart/form-data",
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
     *                  @OA\Property(
     *                     property="store_name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="store_description",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="store_address",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="store_category",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="images[]",
     *                     type="array", 
     *                      @OA\Items(type="string", format="binary")
     *                 ),
     *                 example={
     *                      "firstname": "New", 
     *                      "lastname": "Vendor", 
     *                      "email": "newvendor@yopmail.com", 
     *                      "address": "Test Address", 
     *                      "password": "12345678", 
     *                      "category": "Grocery Store", 
     *                      "package_id": 1, 
     *                      "store_name": "Test store", 
     *                      "store_description": "Test Description", 
     *                      "store_address": "ABC address", 
     *                      "store_category": "Test Category", 
     *                      "images[]": ""
     *                  }
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
                        "data": {}
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

            // fetch selected package data from packages table
            $package = $this->packageRepository->find($request->package_id);

            // vendor package subscription
            $packageSubscribed = $this->packageSubscriptionRepository->create([
                'package_id' => $package->id,
                'vendor_id' => $registerVendor->id,
                'customer_limit' => $package->customer_limit,
            ]);

            if($packageSubscribed)
            {
                // After the subscription, store will be created
                $store = $this->storeRepository->create([
                    'name' => $request->store_name,
                    'description' => $request->store_description,
                    'address' => $request->store_address,
                    'category' => $request->store_category,
                    'vendor_id' => $packageSubscribed->vendor_id,
                    'package_subscription_id' => $packageSubscribed->id
                ]);

                // add images
                if($request->hasFile('images')){
                    if(count($request->images) > 0)
                        foreach ($request->images as $image) {
                            if($image->isValid()) {
                                $store
                                ->addMedia($image)
                                ->toMediaCollection('images', 'media');
                            }       
                        }
                }
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
            // If current password not correct
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

    /**
     * @OA\Post(
     *     path="/api/edit-profile",
     *     summary="Edit profile (Vendor / Customer)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="firstname",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="lastname",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="address",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="phone",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="store_id",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="store_name",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="store_description",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="store_address",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="store_category",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                  @OA\Property(
     *                     property="images[]",
     *                     type="array", 
     *                      @OA\Items(type="string", format="binary")
     *                 ),
     *                 example={}
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
     *             @OA\Examples(example="result", value={}, summary="An result object."),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function editProfile(EditProfileRequest $request)
    {
        try {
            // update user
            $this->repository->update([
                'firstname' => $request->firstname ?? null,
                'lastname' => $request->lastname ?? null,
                'address' => $request->address ?? null,
                'phone' => $request->phone ?? null,
                'firstname' => $request->firstname ?? null,
            ], auth()->user()->id);

            // if user role is vendor
            if(auth()->user()->_role->name == 'vendor')
            {
                // update the store details
                $this->storeRepository->update([
                    'name' => $request->store_name ?? null,
                    'description' => $request->store_description ?? null,
                    'address' => $request->store_address ?? null,
                    'category' => $request->store_category ?? null,
                ], $request->store_id);
                
                // add images
                if($request->hasFile('images')){

                    // fetch store
                    $store = $this->storeRepository->find($request->store_id);

                    if(count($request->images) > 0)
                        foreach ($request->images as $image) {
                            if($image->isValid()) {
                                $store
                                ->addMedia($image)
                                ->toMediaCollection('images', 'media');
                            }       
                        }
                }
            }

            return APIresponse::success('Update successfully!', []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // for both users ( vendor and customer )
    /**
     * @OA\Get(
     * path="/api/show-profile",
     * summary="Show profile",
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
    public function showProfile()
    {
        try {
            // fetch subscription request list
            $data = $this->repository->showProfile();

            // return response
            return APIresponse::success('Fetch successfully!', $data->toArray());
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    // public function forgotPassword(ForgotPasswordRequest $request)
    // {
    //     try {
    //         $status = Password::sendResetLink(
    //             $request->only('email')
    //         );
         
    //         return $status === Password::RESET_LINK_SENT
    //                     ? APIresponse::success('Reset link send!!', $status)
    //                     : APIresponse::error("Something went wrong!", $status);
    //     } catch (\Throwable $th) {
    //         return APIresponse::error($th->getMessage(), []);
    //     }
    // }

    public function forgotPassword(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|exists:users',
            ]);
    
            // Delete all old code that user send before.
            ResetCodePassword::where('email', $request->email)->delete();
    
            // Generate random code
            $data['code'] = mt_rand(100000, 999999);
    
            // Create a new code
            $codeData = ResetCodePassword::create($data);
            $code = $codeData->code ?? '';
            // robertwilliam@yopmail.com
            $html = view('emails.send-code-reset-password', compact('code'))->render();

            PHPCustomMail::customMail('Dev', $request->email, 'Forgot password code!', $html);

            // return response
            return APIresponse::success('Check the reset password code located in your email!', []);
        } catch (\Throwable $th) {
            return APIresponse::error($th->getMessage(), []);
        }
    }

    public function codeCheck(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return APIresponse::error("Code has been expired!", []);
        }

        // find user's email 
        $user = User::firstWhere('email', $passwordReset->email);

        // Typo password converted into Hash format
        $request->merge([ 'password' => Hash::make($request->password)]);

        // update user password
        $user->update($request->only('password'));

        // delete current code 
        $passwordReset->delete();

        // return response
        return APIresponse::success('Password has been successfully reset!', []);
    }
}