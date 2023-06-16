<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\SetupsController;
use App\Http\Controllers\StoresController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/setup', [SetupsController::class, 'index']);
Route::post('/login', [AuthenticationsController::class, 'attemptLogin']);
Route::post('/register/customer', [AuthenticationsController::class, 'registerCustomer']);
Route::post('/register/vendor', [AuthenticationsController::class, 'registerVendor']);

Route::group(['prefix'=>'password'], function(){
    Route::post('/email',  [AuthenticationsController::class, 'forgotPassword']);
    Route::post('/code/check', [AuthenticationsController::class, 'codeCheck']);
});

Route::post('/forgot-password', [AuthenticationsController::class, 'forgotPassword']);

// Route::post('/forgot-password', function (Request $request) {
//     $request->validate(['email' => 'required|email']);
 
//     $status = Password::sendResetLink(
//         $request->only('email')
//     );
 
//     return $status === Password::RESET_LINK_SENT
//                 ? back()->with(['status' => __($status)])
//                 : back()->withErrors(['email' => __($status)]);
// })->middleware('guest')->name('password.email');

Route::middleware('auth:sanctum')->group(function () {
    // all users
    Route::post('/change-password', [AuthenticationsController::class, 'changePassword']);
    Route::post('/edit-profile', [AuthenticationsController::class, 'editProfile']);
    Route::get('/show-profile', [AuthenticationsController::class, 'showProfile']);

    // for customers
    Route::group(['middleware' => ['role:customer']], function () {
        Route::post('/all-stores', [StoresController::class, 'allStores']);
        Route::post('/store-subscription', [StoresController::class, 'storeSubscription']);
        Route::put('/store-unsubscription', [StoresController::class, 'storeUnsubscription']);
        Route::put('/update-store-password', [StoresController::class, 'updateStorePassword']);
        Route::post('/view-store-password', [StoresController::class, 'viewStorePassword']);
    });
    
    // for vendors
    Route::group(['middleware' => ['role:vendor']], function () {
        // Route::get('/store-subscription-requests', [StoresController::class, 'storeSubscriptionRequests']);
        Route::get('/store-requests', [StoresController::class, 'storeRequests']);
        Route::post('/accept-customer-request', [StoresController::class, 'acceptCustomerRequest']);
        Route::post('/reject-customer-request', [StoresController::class, 'rejectCustomerRequest']);
        Route::post('/new-package-subscription',  [StoresController::class, 'newPackageSubscription']);
        Route::get('/remove-store-image/{uuid}',  [StoresController::class, 'removeStoreImage']);
    });
    
});